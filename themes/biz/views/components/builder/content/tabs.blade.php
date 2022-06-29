<div @class($uniqueClass)>
    <tabs
        :classes="{{ Illuminate\Support\Js::from($classes) }}"
        :content="{{ Illuminate\Support\Js::from($tabsContent) }}"
    />
</div>
