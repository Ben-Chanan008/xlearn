# Course sections: steps 4 and 5

Steps 1–3 are done: content schema (`content_mode`, `due_at`, `files` as a JSON list), the create page at `sections/create`, and the student display on the learn page.

- **Step 4:** edit an existing section and its content on a full page.
- **Step 5:** delete a section.

Each step ships on its own and gets its own commit.

---

## Open questions

These need your call before the step they affect. Each has a recommendation.

| # | Question | Recommendation | Affects |
|---|----------|----------------|---------|
| 1 | Where does the HTML sanitizer go, now that create and edit both need it? | A trait, `App\Http\Requests\Concerns\SanitizesLessonHtml`, used by both FormRequests. It's request preparation, so it stays next to the requests. | 4a |
| 2 | The inline rename already uses `PATCH sections/{courseSection}` and `UpdateCourseSectionRequest`. What does the full edit use? | Move rename to `PATCH sections/{courseSection}/name` with `RenameCourseSectionRequest`. The full edit gets `PUT sections/{courseSection}` with `UpdateCourseSectionRequest`, which matches Laravel's resource naming. | 4a |
| 3 | When an instructor removes a file from a content item that stays, what happens to the file on disk? | Delete it from storage after the save commits. Otherwise the disk fills up with orphaned lecture videos. | 4b |
| 4 | When an instructor removes a whole content item, which may have student submissions, what happens? | Soft delete the item. Its files and the submissions stay, and it disappears from every page. Same rule as sections. | 4b |
| 5 | An assignment's due date has already passed and the instructor edits something else. | Allow saving an unchanged past date. A due date that is new or changed must still be in the future. | 4b |

---

## Step 4: Edit page

### 4a. Groundwork (no visible change)

1. **Sanitizer:** move the `HtmlSanitizer` setup out of `StoreCourseSectionRequest::prepareForValidation()` into the place chosen in question 1. `StoreCourseSectionRequest` keeps working exactly as before.
2. **Rename route:** move the inline rename to the route and request chosen in question 2. Update `sectionUrl()` in `course-management.blade.php` to match. Renaming must work the same afterwards.
3. **Soft deletes:** add `SoftDeletes` to `CourseSectionsContent`. The `deleted_at` column already exists. Without the trait, removed items would either be hard-deleted, taking students' submissions with them through the FK cascade, or would still show up.
4. **Content order:** order `CourseSection::sectionContents()` by `id`, so items keep the order the instructor built them in. Today it relies on SQLite's default order.

### 4b. Backend

**Routes** (instructor group, `can('manage', 'course')`, `scopeBindings()`):
- `GET courses/{course}/sections/{courseSection}/edit` goes to `CourseSectionController@edit`.
- `PUT courses/{course}/sections/{courseSection}` goes to `CourseSectionController@update`. The form is sent as `POST` with `_method=PUT`, because PHP doesn't parse multipart file uploads on a real `PUT`.

**What the edit form sends:** the same fields as create, plus:
- `contents[i][id]`: present on existing items, missing on new ones.
- `contents[i][keep_files][]`: the paths of existing files the instructor kept.

**`UpdateCourseSectionRequest`:**
- Same rules as the store request, through the shared sanitizer.
- `contents.*.id`: nullable, and it must belong to *this* section. A rule checks the ID against `$this->route('courseSection')->sectionContents()`, so an item from another section can't be edited by changing the ID.
- `contents.*.files` is no longer `required_if` on its own. An `after()` hook checks that each item in `files` or `both` mode ends up with at least one file, counting kept and new together.
- `contents.*.due_at`: `after:now` applies only when the value differs from the stored one (question 5).

**`CourseSectionController@update`**, all in one transaction:
1. Update `section_name`.
2. Soft-delete existing items that aren't in the request (question 4).
3. For each submitted item:
   - **Kept files:** take the item's *stored* `files` and keep only the entries whose `path` appears in `keep_files`. Client paths are never trusted directly, so a forged path can't attach someone else's file.
   - **New uploads:** stored like `store()` does, and tracked for cleanup.
   - **Mode switches:** switching to `text` drops all files. Switching to `files` sets `content_information` to null.
   - **Assignment:** same as create. Unchecking it sets `content_type` to `information` and clears `due_at`. Existing submissions are untouched.
   - **New items** (no `id`) are created like in `store()`.
4. **After commit:** delete files that were removed from items that still exist (question 3).
5. **On failure:** delete the new uploads, the same as `store()`.
6. **Response:** flash `success`, then JSON `{ message, redirect }` to the course management page, the same shape as store.

### 4c. Page

1. **One view for both pages:** rename `section-create.blade.php` to `section-form.blade.php`, used by `create` and `edit`. Pass `$section` (null on create), the form action and the method. Title, button text and the `_method` field change with `$section`.
2. **Pre-filled items:** existing items are passed with `Js::from()` as `{ id, content_name, content_mode, content_information, files, is_assignment, due_at }`. `addContentItem(data = null)` fills an item from that:
   - **Lesson text** is loaded into Quill with `quill.clipboard.dangerouslyPasteHTML()`. That's safe because it was sanitized on save.
   - **Existing files** go in the file list with an "uploaded" tag and the same remove button. Removing one drops it from `keep_files`.
   - **Due date** is converted from UTC ISO to the local `YYYY-MM-DDTHH:mm` value that `datetime-local` expects.
3. **Form data:** `buildFormData()` adds `contents[i][id]` and `contents[i][keep_files][]`.
4. **Warning before removing an item:** removing an existing item asks "Students will no longer see this item" with a `confirm()`. New items are removed without asking.
5. **Entry point:** add an **Edit** link (`fa-pen-to-square`) to each section row on the course management page, next to the rename pencil.

### 4d. Verification

- **Feature tests** (in-memory DB):
  - edit page renders the existing items
  - rename an item; keep one file and drop another (the dropped file is deleted from disk)
  - add a new item; remove an item (soft-deleted, its submissions remain)
  - switch an item's mode
  - a forged `id` from another section is rejected
  - a forged `keep_files` path is ignored
  - an unchanged past due date passes; a changed one in the past fails
  - another instructor gets 403
- **In the browser:** edit a real section, check Quill pre-fills, then check the student page shows the changes.

---

## Step 5: Delete a section

### Backend
- **Route:** `DELETE courses/{course}/sections/{courseSection}` goes to `CourseSectionController@destroy`, with `can('manage', 'course')` and `scopeBindings()`.
- **`destroy()`**, in one transaction:
  1. Soft-delete the section. `CourseSection` already uses `SoftDeletes`.
  2. Renumber: `decrement('position')` on the course's sections whose `position` is greater than the deleted one's, so positions stay 1..n.
- **Contents:** items, files and submissions are left alone. They're hidden because the section is, and stay recoverable.
- **Response:** JSON `{ message }`. On error, 500 with `Something went wrong`, matching the other actions.
- **Already handled:** soft-deleted sections drop out of `Course::courseSections()`, the student learn and grades pages, and route binding. The edit page and rename for a deleted section return 404 with no extra code.

### Page (`course-management.blade.php`)
- **Trash button:** add one to each row (`data-delete-section`), handled by the existing click listener on the list.
- **Confirm:** a `confirm()` naming the section and how many content items it has. The count comes from `withCount('sectionContents')` in `showCourseManagementPage`, put on the row as `data-content-count`.
- **Request:** `fetch` with `DELETE` and the same headers as rename. On success, remove the row, renumber the visible numbers, show the success popup, and hide the list if it's empty. On error, show the error popup.

### Verification
- **Feature tests:**
  - deleting soft-deletes the section and renumbers the later ones (1, 2, 3, 4 with 2 deleted becomes 1, 2, 3)
  - the section disappears from the student learn page
  - a section from another course returns 404
  - another instructor gets 403
- **In the browser:** delete a middle section and check the numbering, then reload and check it again.

---

## Out of scope (noted for later)

- **Reordering** sections or content items (`position` on sections already supports it).
- **Restoring** soft-deleted sections or items.
- **Assignment submission:** `CourseController::submitAssignment` has a `dd()` and writes `students_id` when the column is `student_id`.
- **Security advisories:** `composer audit` reports 55 advisories in existing packages.
