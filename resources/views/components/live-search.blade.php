{{--
    Searches on the server as you type, so it covers every page of a paginated list.
    The page wraps its results (list + pagination links) in data-search-results; this swaps that block for the server's new one.
--}}
@props(['placeholder' => 'Search...'])

<form
    {{ $attributes->merge(['class' => 'mb-8']) }}
    x-data="{
        query: @js(request('search', '')),
        latest: 0,
        async search() {
            const url = new URL(window.location.href);
            this.query.trim() ? url.searchParams.set('search', this.query.trim()) : url.searchParams.delete('search');
            url.searchParams.delete('page');

            // Only the newest request may update the page, so a slow older one can't overwrite it
            const request = ++this.latest;
            const html = await fetch(url).then(res => res.text());
            if (request !== this.latest) return;

            const fresh = new DOMParser().parseFromString(html, 'text/html').querySelector('[data-search-results]');
            document.querySelector('[data-search-results]').replaceWith(fresh);
            history.replaceState(null, '', url);
        },
    }"
    @submit.prevent="search()"
>
    <x-search-bar name="search" x-model="query" x-on:input.debounce.300ms="search()" :placeholder="$placeholder" />
</form>
