<div>
    <x-action.button
        @class(['copy-current-link' => in_array($type, ['copy', 'instagram'], true), 'rounded-full' => $iconOnly])
        :id="$buttonId"
        :label="$label"
        :icon="$icon"
        :icon-start="true"
        :url="$url"
        size="medium"
        :type="$buttonType"
        :target="$target"
        :iconOnly="$iconOnly"
        :icon-class="$iconOnly ? 'icon icon-4' : 'icon icon-4'"
        :wire-click="$actionOnClick"
        :handle-livewire-loading="true"
        :title="$title"
    />
</div>
