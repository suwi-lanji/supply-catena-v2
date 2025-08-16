<?php if (isset($component)) { $__componentOriginal511d4862ff04963c3c16115c05a86a9d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal511d4862ff04963c3c16115c05a86a9d = $attributes; } ?>
<?php $component = Illuminate\View\DynamicComponent::resolve(['component' => $getFieldWrapperView()] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dynamic-component'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\DynamicComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['field' => $field]); ?>

    <?php
        $containers = $getChildComponentContainers();

        $addAction = $getAction($getAddActionName());
        $cloneAction = $getAction($getCloneActionName());
        $deleteAction = $getAction($getDeleteActionName());
        $moveDownAction = $getAction($getMoveDownActionName());
        $moveUpAction = $getAction($getMoveUpActionName());
        $reorderAction = $getAction($getReorderActionName());

        $isAddable = $isAddable();
        $isCloneable = $isCloneable();
        $isCollapsible = $isCollapsible();
        $isDeletable = $isDeletable();
        $isReorderable = $isReorderable();
        $isReorderableWithButtons = $isReorderableWithButtons();
        $isReorderableWithDragAndDrop = $isReorderableWithDragAndDrop();

        $statePath = $getStatePath();

        $columnLabels = $getColumnLabels();
        $colStyles = $getColStyles();

    ?>

    <div
        
        x-data="{ isCollapsed: <?php echo \Illuminate\Support\Js::from($isCollapsed())->toHtml() ?> }"
        x-on:repeater-collapse.window="$event.detail === '<?php echo e($getStatePath()); ?>' && (isCollapsed = true)"
        x-on:repeater-expand.window="$event.detail === '<?php echo e($getStatePath()); ?>' && (isCollapsed = false)"

        <?php echo e($attributes
                ->merge($getExtraAttributes(), escape: false)
                ->class(['bg-white border border-gray-300 shadow-sm rounded-xl relative dark:bg-gray-900 dark:border-gray-600'])); ?>

    >

        <div class="<?php echo \Illuminate\Support\Arr::toCssClasses([
            'filament-tables-header',
            'flex items-center h-10 overflow-hidden border-b bg-gray-50 rounded-t-xl',
            'dark:bg-gray-900 dark:border-gray-700',
        ]); ?>">

            <div class="flex-1"></div>
            <!--[if BLOCK]><![endif]--><?php if($isCollapsible): ?>
                <div>
                    <button
                        x-on:click="isCollapsed = !isCollapsed"
                        type="button"
                        class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                            'flex items-center justify-center flex-none w-10 h-10 text-gray-400 transition hover:text-gray-300',
                            'dark:text-gray-400 dark:hover:text-gray-500',
                        ]); ?>"
                    >
                        <?php if (isset($component)) { $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c = $attributes; } ?>
<?php $component = BladeUI\Icons\Components\Svg::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('heroicon-s-minus-small'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\BladeUI\Icons\Components\Svg::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'w-4 h-4','x-show' => '! isCollapsed']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c)): ?>
<?php $attributes = $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c; ?>
<?php unset($__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal643fe1b47aec0b76658e1a0200b34b2c)): ?>
<?php $component = $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c; ?>
<?php unset($__componentOriginal643fe1b47aec0b76658e1a0200b34b2c); ?>
<?php endif; ?>

                        <span class="sr-only" x-show="! isCollapsed">
                            <?php echo e(__('forms::components.repeater.buttons.collapse_item.label')); ?>

                        </span>

                        <?php if (isset($component)) { $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c = $attributes; } ?>
<?php $component = BladeUI\Icons\Components\Svg::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('heroicon-s-plus-small'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\BladeUI\Icons\Components\Svg::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'w-4 h-4','x-show' => 'isCollapsed','x-cloak' => true]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c)): ?>
<?php $attributes = $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c; ?>
<?php unset($__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal643fe1b47aec0b76658e1a0200b34b2c)): ?>
<?php $component = $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c; ?>
<?php unset($__componentOriginal643fe1b47aec0b76658e1a0200b34b2c); ?>
<?php endif; ?>

                        <span class="sr-only" x-show="isCollapsed" x-cloak>
                            <?php echo e(__('forms::components.repeater.buttons.expand_item.label')); ?>

                        </span>
                    </button>
                </div>
            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
        </div>

        <div class="px-4">
            <table class=" filament-table-repeater w-full text-left rtl:text-right table-auto mx-4" x-show="! isCollapsed">
                <thead>
                    <tr>

                        <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $columnLabels; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $columnLabel): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <!--[if BLOCK]><![endif]--><?php if($columnLabel['display']): ?>
                            <th class="filament-table-repeater-header-cell p-2"
                                <?php if($colStyles && isset($colStyles[$columnLabel['component']])): ?>
                                    style="<?php echo e($colStyles[$columnLabel['component']]); ?>"
                                <?php endif; ?>
                            >
                                <span>
                                    <?php echo e($columnLabel['name']); ?>

                                </span>
                            </th>
                            <?php else: ?>
                            <th style="display: none"></th>
                            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->

                        <!--[if BLOCK]><![endif]--><?php if($isReorderableWithDragAndDrop || $isReorderableWithButtons || $isCloneable || $isDeletable): ?>
                        	<th></th>
						<?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                    </tr>
                </thead>

                <tbody
                    <?php if($isReorderable): ?>
                        :wire:end.stop="'mountFormComponentAction(\'' . $statePath . '\', \'reorder\', { items: $event.target.sortable.toArray() })'"
                       x-sortable
                    <?php endif; ?>
                >

                    <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $containers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $uuid => $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>

                        <tr
                            x-on:repeater-collapse.window="$event.detail === '<?php echo e($getStatePath()); ?>' && (isCollapsed = true)"
                            x-on:repeater-expand.window="$event.detail === '<?php echo e($getStatePath()); ?>' && (isCollapsed = false)"
                            wire:key="<?php echo e($this->getId()); ?>.<?php echo e($item->getStatePath()); ?>.<?php echo e($field::class); ?>.item"
                            x-sortable-item="<?php echo e($uuid); ?>"
                        >

                            <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $item->getComponents(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $component): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <td
                                class="filament-table-repeater-tbody-cell px-1 align-top"
                                <?php if($component->isHidden() || ($component instanceof \Filament\Forms\Components\Hidden)): ?>style="display:none"<?php endif; ?>
                                <?php if($colStyles && isset($colStyles[$component->getName()])): ?>
                                    style="$colStyles[$component->getName()]"
                                <?php endif; ?>
                            >
                                <?php echo e($component); ?>

                            </td>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->

                            <!--[if BLOCK]><![endif]--><?php if($isReorderableWithDragAndDrop || $isReorderableWithButtons || filled($itemLabel) || $isCloneable || $isDeletable || $isCollapsible): ?>
								<td class="flex items-center gap-x-3 py-2 max-w-20">
                                    <!--[if BLOCK]><![endif]--><?php if($isReorderableWithDragAndDrop || $isReorderableWithButtons): ?>
                                        <!--[if BLOCK]><![endif]--><?php if($isReorderableWithDragAndDrop): ?>
                                            <div x-sortable-handle>
                                                <?php echo e($reorderAction); ?>

                                            </div>
                                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

                                        <!--[if BLOCK]><![endif]--><?php if($isReorderableWithButtons): ?>
                                            <div
                                                class="flex items-center justify-center"
                                            >
                                                <?php echo e($moveUpAction(['item' => $uuid])->disabled($loop->first)); ?>

                                            </div>

                                            <div
                                                class="flex items-center justify-center"
                                            >
                                                <?php echo e($moveDownAction(['item' => $uuid])->disabled($loop->last)); ?>

                                            </div>
                                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

                                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

                                    <!--[if BLOCK]><![endif]--><?php if($isCloneable || $isDeletable ): ?>
                                        <!--[if BLOCK]><![endif]--><?php if($cloneAction->isVisible()): ?>
                                            <div>
                                                <?php echo e($cloneAction(['item' => $uuid])); ?>

                                            </div>
                                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

                                        <!--[if BLOCK]><![endif]--><?php if($isDeletable): ?>
                                            <div>
                                                <?php echo e($deleteAction(['item' => $uuid])); ?>

                                            </div>
                                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

                                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

                                </td>
							<?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                </tbody>

            </table>

            <div class="p-2 text-xs text-center text-gray-400" x-show="isCollapsed" x-cloak>
                <?php echo e(__('filament-table-repeater::components.table-repeater.collapsed')); ?>

            </div>
        </div>

        <!--[if BLOCK]><![endif]--><?php if($isAddable): ?>
            <div class="relative flex justify-center py-2">
                <?php echo e($addAction); ?>

            </div>
        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

    </div>

 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal511d4862ff04963c3c16115c05a86a9d)): ?>
<?php $attributes = $__attributesOriginal511d4862ff04963c3c16115c05a86a9d; ?>
<?php unset($__attributesOriginal511d4862ff04963c3c16115c05a86a9d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal511d4862ff04963c3c16115c05a86a9d)): ?>
<?php $component = $__componentOriginal511d4862ff04963c3c16115c05a86a9d; ?>
<?php unset($__componentOriginal511d4862ff04963c3c16115c05a86a9d); ?>
<?php endif; ?>
<?php /**PATH /home/suwilanji/dev/supply-catena/resources/views/vendor/filament-table-repeater/table-repeater.blade.php ENDPATH**/ ?>