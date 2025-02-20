<?php

namespace DigitalNode\Larafields\Services;

use Illuminate\Support\Collection;
use Livewire\Livewire;

class FormRenderer
{
    public function renderLivewireForm(array $group, array $additionalParams = []): string
    {
        return Livewire::mount('FormMaker', array_merge(
            ['group' => $group],
            $additionalParams
        ));
    }

    public function renderTermMetaBox(array $group): string
    {
        return $this->wrapInPostBox(
            $group['label'],
            $this->renderLivewireForm($group)
        );
    }

    public function renderUserGroups(Collection $groups, $user): string
    {
        return $groups->map(function (array $group) use ($user): string {
            return $this->wrapInPostBox(
                $group['label'],
                $this->renderLivewireForm($group, ['userContext' => $user->ID])
            );
        })->implode('');
    }

    private function wrapInPostBox(string $title, string $content): string
    {
        return sprintf(
            '<div id="poststuff">
                <div class="form-field postbox">
                    <div class="postbox-header">
                        <h2>%s</h2>
                    </div>
                    <div class="inside">
                        %s
                    </div>
                </div>
            </div>',
            $title,
            $content
        );
    }
}
