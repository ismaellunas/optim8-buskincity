<div @class($entity['id'])>
    <tabs
        :classes="{{ Illuminate\Support\Js::from($classes) }}"
        :content="{{ Illuminate\Support\Js::from($tabsContent) }}"
    />
</div>