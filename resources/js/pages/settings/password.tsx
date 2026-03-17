import PasswordController from '@/actions/App/Http/Controllers/Settings/PasswordController';
import InputError from '@/components/input-error';
import AppLayout from '@/layouts/app-layout';
import SettingsLayout from '@/layouts/settings/layout';
import { type BreadcrumbItem } from '@/types';
import { Transition } from '@headlessui/react';
import { Form, Head } from '@inertiajs/react';
import { Eye, EyeOff } from 'lucide-react';
import { useRef, useState } from 'react';

import HeadingSmall from '@/components/heading-small';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { edit } from '@/routes/user-password';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Password settings',
        href: edit().url,
    },
];

export default function Password() {
    const passwordInput = useRef<HTMLInputElement>(null);
    const currentPasswordInput = useRef<HTMLInputElement>(null);
    const [showCurrentPassword, setShowCurrentPassword] = useState(false);
    const [showPassword, setShowPassword] = useState(false);
    const [showPasswordConfirmation, setShowPasswordConfirmation] =
        useState(false);

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Password settings" />

            <SettingsLayout>
                <div className="space-y-6">
                    <HeadingSmall
                        title="Update password"
                        description="Ensure your account is using a long, random password to stay secure"
                    />

                    <Form
                        {...PasswordController.update.form()}
                        options={{
                            preserveScroll: true,
                        }}
                        resetOnError={[
                            'password',
                            'password_confirmation',
                            'current_password',
                        ]}
                        resetOnSuccess
                        onError={(errors) => {
                            if (errors.password) {
                                passwordInput.current?.focus();
                            }

                            if (errors.current_password) {
                                currentPasswordInput.current?.focus();
                            }
                        }}
                        className="space-y-6"
                    >
                        {({ errors, processing, recentlySuccessful }) => (
                            <>
                                <div className="grid gap-2">
                                    <Label htmlFor="current_password">
                                        Current password
                                    </Label>

                                    <div className="relative">
                                        <Input
                                            id="current_password"
                                            ref={currentPasswordInput}
                                            name="current_password"
                                            type={
                                                showCurrentPassword
                                                    ? 'text'
                                                    : 'password'
                                            }
                                            className="mt-1 block w-full pr-10"
                                            autoComplete="current-password"
                                            placeholder="Current password"
                                        />
                                        <button
                                            type="button"
                                            onClick={() =>
                                                setShowCurrentPassword(
                                                    (prev) => !prev,
                                                )
                                            }
                                            className="absolute right-2 top-1/2 mt-0.5 -translate-y-1/2 rounded p-1.5 text-muted-foreground hover:bg-muted hover:text-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2"
                                            aria-label={
                                                showCurrentPassword
                                                    ? 'Hide password'
                                                    : 'Show password'
                                            }
                                        >
                                            {showCurrentPassword ? (
                                                <EyeOff
                                                    className="size-4"
                                                    aria-hidden
                                                />
                                            ) : (
                                                <Eye
                                                    className="size-4"
                                                    aria-hidden
                                                />
                                            )}
                                        </button>
                                    </div>

                                    <InputError
                                        message={errors.current_password}
                                    />
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="password">
                                        New password
                                    </Label>

                                    <div className="relative">
                                        <Input
                                            id="password"
                                            ref={passwordInput}
                                            name="password"
                                            type={
                                                showPassword ? 'text' : 'password'
                                            }
                                            className="mt-1 block w-full pr-10"
                                            autoComplete="new-password"
                                            placeholder="New password"
                                        />
                                        <button
                                            type="button"
                                            onClick={() =>
                                                setShowPassword(
                                                    (prev) => !prev,
                                                )
                                            }
                                            className="absolute right-2 top-1/2 mt-0.5 -translate-y-1/2 rounded p-1.5 text-muted-foreground hover:bg-muted hover:text-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2"
                                            aria-label={
                                                showPassword
                                                    ? 'Hide password'
                                                    : 'Show password'
                                            }
                                        >
                                            {showPassword ? (
                                                <EyeOff
                                                    className="size-4"
                                                    aria-hidden
                                                />
                                            ) : (
                                                <Eye
                                                    className="size-4"
                                                    aria-hidden
                                                />
                                            )}
                                        </button>
                                    </div>

                                    <p className="text-xs text-muted-foreground">
                                        Must contain at least 8 characters,
                                        one uppercase letter, one lowercase
                                        letter, one number, and one special
                                        character.
                                    </p>

                                    <InputError message={errors.password} />
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="password_confirmation">
                                        Confirm password
                                    </Label>

                                    <div className="relative">
                                        <Input
                                            id="password_confirmation"
                                            name="password_confirmation"
                                            type={
                                                showPasswordConfirmation
                                                    ? 'text'
                                                    : 'password'
                                            }
                                            className="mt-1 block w-full pr-10"
                                            autoComplete="new-password"
                                            placeholder="Confirm password"
                                        />
                                        <button
                                            type="button"
                                            onClick={() =>
                                                setShowPasswordConfirmation(
                                                    (prev) => !prev,
                                                )
                                            }
                                            className="absolute right-2 top-1/2 mt-0.5 -translate-y-1/2 rounded p-1.5 text-muted-foreground hover:bg-muted hover:text-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2"
                                            aria-label={
                                                showPasswordConfirmation
                                                    ? 'Hide password'
                                                    : 'Show password'
                                            }
                                        >
                                            {showPasswordConfirmation ? (
                                                <EyeOff
                                                    className="size-4"
                                                    aria-hidden
                                                />
                                            ) : (
                                                <Eye
                                                    className="size-4"
                                                    aria-hidden
                                                />
                                            )}
                                        </button>
                                    </div>

                                    <InputError
                                        message={errors.password_confirmation}
                                    />
                                </div>

                                <div className="flex items-center gap-4">
                                    <Button
                                        disabled={processing}
                                        data-test="update-password-button"
                                    >
                                        {processing && <Spinner />}
                                        Save password
                                    </Button>

                                    <Transition
                                        show={recentlySuccessful}
                                        enter="transition ease-in-out"
                                        enterFrom="opacity-0"
                                        leave="transition ease-in-out"
                                        leaveTo="opacity-0"
                                    >
                                        <p className="text-sm text-neutral-600">
                                            Saved
                                        </p>
                                    </Transition>
                                </div>
                            </>
                        )}
                    </Form>
                </div>
            </SettingsLayout>
        </AppLayout>
    );
}
