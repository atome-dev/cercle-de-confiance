<section class="w-full max-w-4xl mx-auto px-4 py-10 sm:px-6">

    <flux:heading class="sr-only">{{ __('Security settings') }}</flux:heading>

    <x-pages::settings.layout :heading="__('Update password')" :subheading="__('Ensure your account is using a long, random password to stay secure')">
        <flux:card class="mt-6 p-6 sm:p-8">
            <form method="POST" wire:submit="updatePassword" class="space-y-6">
                <flux:input
                    wire:model="current_password"
                    :label="__('Current password')"
                    type="password"
                    icon="lock-closed"
                    required
                    autocomplete="current-password"
                    viewable
                />
                <flux:input
                    wire:model="password"
                    :label="__('New password')"
                    type="password"
                    icon="key"
                    required
                    autocomplete="new-password"
                    passwordrules="{{ \Illuminate\Validation\Rules\Password::defaults()->toPasswordRulesString() }}"
                    viewable
                />
                <flux:input
                    wire:model="password_confirmation"
                    :label="__('Confirm password')"
                    type="password"
                    icon="key"
                    required
                    autocomplete="new-password"
                    passwordrules="{{ \Illuminate\Validation\Rules\Password::defaults()->toPasswordRulesString() }}"
                    viewable
                />

                <div class="flex items-center gap-4">
                    <flux:button variant="primary" type="submit" icon="check" data-test="update-password-button">
                        {{ __('Save') }}
                    </flux:button>
                </div>
            </form>
        </flux:card>
    </x-pages::settings.layout>
</section>
