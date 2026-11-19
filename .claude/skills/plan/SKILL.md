---
name: plan
description: Use whenever you read or work from a plan in plans/*.md, before starting the "next step", or when asked which steps are done. Checks each "## Step" heading for a #[done] marker above it, so you know what's already completed and which step comes next.
---

# Plan progress for xlearn

Plans live in `plans/*.md`. Each step is a `## Step N: ...` heading. The user marks a finished step by putting `#[done]` on its own line above the heading. The marker can be in any case (`#[Done]` is common), and it may sit above or below the `---` separator:

```markdown
---
#[Done]

## Step 1: Groundwork and the bell
```

## 1. Find out what's done

If the user named a plan, check that one. Otherwise check the plan you're working from, or all of them. Run this in Bash:

```bash
awk -f .claude/skills/plan/steps.awk plans/notifications.md
```

Pass `plans/*.md` to check every plan. The script lives in its own file because the skill loader replaces dollar-number placeholders in this file with the skill's arguments, which would break an inline awk program.

A step only counts as done if the marker is the last thing before its heading, ignoring blank lines and `---`. A marker followed by other text belongs to nothing, so treat the step as not done.

## 2. Use it

- **The next step** is the first `[todo]` step. Say which one it is before you start it.
- **Don't redo done steps.** Their code already exists. Read it when a later step depends on it, but don't change it unless the user asks.
- **A later step marked done while an earlier one isn't** may be deliberate, because some steps are optional or skipped. Mention it, but don't assume either way.
- **Read the whole step before you start**, including its code blocks. The user leaves inline comments in plans, sometimes inside code blocks.

## 3. Markers belong to the user

Never add, move or remove a `#[done]` marker yourself. If you finish a step, say so and let the user mark it. Add one only when the user explicitly asks you to.
