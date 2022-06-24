<style>
@foreach ($styledComponents as $styleBlocks)
@foreach ($styleBlocks as $styleBlock)
{{ $styleBlock->toText() }}
@endforeach
@endforeach
</style>