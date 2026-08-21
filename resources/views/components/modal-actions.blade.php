@props(['label'])
<div class="flex justify-end gap-2 pt-2">
    <flux:modal.close><flux:button variant="ghost">Cancel</flux:button></flux:modal.close>
    <flux:button type="submit" variant="primary">{{ $label }}</flux:button>
</div>
