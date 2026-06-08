<div>
    <div class="flex items-end justify-between gap-4">
        <div>
            <flux:heading size="xl" level="1">SSO Providers</flux:heading>
            <flux:text class="mt-2">Manage single sign-on identity providers.</flux:text>
        </div>
        <flux:button variant="primary" icon="plus" wire:click="openCreateModal" class="cursor-pointer">
            Add Provider
        </flux:button>
    </div>

    <flux:separator variant="subtle" class="my-6" />

    <div class="overflow-hidden rounded-xl border border-zinc-200 dark:border-zinc-700">
        <table class="w-full text-left text-sm">
            <thead class="bg-zinc-50 dark:bg-zinc-800/50">
                <tr class="text-zinc-500 dark:text-zinc-400">
                    <th class="px-4 py-3 font-medium">Provider</th>
                    <th class="px-4 py-3 font-medium">Status</th>
                    <th class="hidden px-4 py-3 font-medium sm:table-cell">Order</th>
                    <th class="px-4 py-3 text-right font-medium">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                @forelse ($providers as $provider)
                    <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/40">
                        <td class="px-4 py-3">
                            <div class="font-medium text-zinc-900 dark:text-zinc-100">{{ $provider->name }}</div>
                            <div class="text-xs text-zinc-500 dark:text-zinc-400">
                                {{ $driverOptions[$provider->driver] ?? ucfirst($provider->driver) }}
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            @if ($provider->is_active)
                                <flux:badge color="green" size="sm">Active</flux:badge>
                            @else
                                <flux:badge color="zinc" size="sm">Inactive</flux:badge>
                            @endif
                        </td>
                        <td class="hidden px-4 py-3 text-zinc-600 dark:text-zinc-300 sm:table-cell">
                            {{ $provider->sort_order }}
                        </td>
                        <td class="px-4 py-3 text-right">
                            <flux:dropdown position="bottom" align="end">
                                <flux:button variant="ghost" size="sm" icon="ellipsis" class="cursor-pointer" />
                                <flux:menu>
                                    <flux:menu.item icon="pencil" wire:click="openEditModal('{{ $provider->uuid }}')">
                                        Edit
                                    </flux:menu.item>
                                    <flux:menu.item
                                        icon="{{ $provider->is_active ? 'eye-off' : 'eye' }}"
                                        wire:click="toggleActive('{{ $provider->uuid }}')"
                                    >
                                        {{ $provider->is_active ? 'Disable' : 'Enable' }}
                                    </flux:menu.item>
                                    <flux:menu.separator />
                                    <flux:menu.item
                                        icon="trash-2"
                                        variant="danger"
                                        wire:click="confirmDelete('{{ $provider->uuid }}')"
                                    >
                                        Delete
                                    </flux:menu.item>
                                </flux:menu>
                            </flux:dropdown>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-4 py-10 text-center text-zinc-500 dark:text-zinc-400">
                            No SSO providers configured yet.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Create / Edit modal --}}
    <flux:modal wire:model.self="showModal" class="w-full max-w-2xl">
        <form wire:submit="save" class="space-y-6">
            <div>
                <flux:heading size="lg">{{ $isEditing ? 'Edit' : 'Add' }} SSO Provider</flux:heading>
                <flux:text class="mt-1">Configure the OAuth/OIDC credentials for this provider.</flux:text>
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <flux:input wire:model="name" label="Name" placeholder="Company Google" />

                <flux:select wire:model.live="driver" label="Driver" placeholder="Choose a driver…">
                    @foreach ($driverOptions as $value => $label)
                        <flux:select.option value="{{ $value }}">{{ $label }}</flux:select.option>
                    @endforeach
                </flux:select>

                <flux:input wire:model="client_id" label="Client ID" />

                <flux:input
                    wire:model="client_secret"
                    type="password"
                    label="Client Secret"
                    :placeholder="$isEditing ? 'Leave blank to keep current' : ''"
                />
            </div>

            @if ($this->driverFields())
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    @foreach ($this->driverFields() as $field)
                        <flux:input
                            wire:model="driverConfig.{{ $field }}"
                            label="{{ ucwords(str_replace('_', ' ', $field)) }}"
                        />
                    @endforeach
                </div>
            @endif

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <flux:input wire:model="redirect_url" label="Redirect URL" badge="Optional"
                    placeholder="Auto-generated from sso.callback" />
                <flux:input wire:model="button_label" label="Button Label" badge="Optional" />
                <flux:input wire:model="icon" label="Icon" badge="Optional"
                    placeholder="Icon name or logo URL" />
                <flux:input wire:model="sort_order" type="number" label="Sort Order" />
            </div>

            <div class="flex flex-wrap items-center gap-6">
                <flux:switch wire:model="is_active" label="Active" />
                <flux:switch wire:model="show_logo" label="Show logo on login" />
            </div>

            <div class="flex justify-end gap-2">
                <flux:button variant="ghost" wire:click="closeModal" class="cursor-pointer">Cancel</flux:button>
                <flux:button variant="primary" type="submit" class="cursor-pointer">
                    {{ $isEditing ? 'Update' : 'Create' }}
                </flux:button>
            </div>
        </form>
    </flux:modal>

    {{-- Delete confirmation modal --}}
    <flux:modal wire:model.self="showDeleteModal" class="w-full max-w-md">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Delete SSO Provider</flux:heading>
                <flux:text class="mt-1">
                    Are you sure? Users who signed in through this provider will no longer be able to use it.
                </flux:text>
            </div>
            <div class="flex justify-end gap-2">
                <flux:button variant="ghost" wire:click="closeDeleteModal" class="cursor-pointer">Cancel</flux:button>
                <flux:button variant="danger" wire:click="delete" class="cursor-pointer">Delete</flux:button>
            </div>
        </div>
    </flux:modal>
</div>
