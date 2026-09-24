<!--[if BLOCK]><![endif]--><?php if(filament()->hasUnsavedChangesAlerts()): ?>
        <?php
        $__scriptKey = '2765487968-0';
        ob_start();
    ?>
        <script>
            window.addEventListener('beforeunload', (event) => {
                if (typeof window.Livewire.find('<?php echo e($_instance->getId()); ?>') === 'undefined') {
                    return
                }

                if (
                    [
                        ...(<?php echo App\Helpers\AssetHelper::js($this instanceof \Filament\Actions\Contracts\HasActions); ?> ? ($wire.mountedActions ?? []) : []),
                        ...(<?php echo App\Helpers\AssetHelper::js($this instanceof \Filament\Forms\Contracts\HasForms); ?>
                            ? ($wire.mountedFormComponentActions ?? [])
                            : []),
                        ...(<?php echo App\Helpers\AssetHelper::js($this instanceof \Filament\Infolists\Contracts\HasInfolists); ?>
                            ? ($wire.mountedInfolistActions ?? [])
                            : []),
                        ...(<?php echo App\Helpers\AssetHelper::js($this instanceof \Filament\Tables\Contracts\HasTable); ?>
                            ? [
                                  ...($wire.mountedTableActions ?? []),
                                  ...($wire.mountedTableBulkAction
                                      ? [$wire.mountedTableBulkAction]
                                      : []),
                              ]
                            : []),
                    ].length &&
                    !$wire?.__instance?.effects?.redirect
                ) {
                    event.preventDefault()
                    event.returnValue = true

                    return
                }
            })
        </script>
        <?php
        $__output = ob_get_clean();

        \Livewire\store($this)->push('scripts', $__output, $__scriptKey)
    ?>
<?php endif; ?><!--[if ENDBLOCK]><![endif]-->
<?php /**PATH /home/puskomdev/ptsp_mtsn2km/vendor/filament/filament/resources/views/components/unsaved-action-changes-alert.blade.php ENDPATH**/ ?>