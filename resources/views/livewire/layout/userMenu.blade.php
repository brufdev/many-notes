<div>
    <x-menu anchorElement="$refs.header" anchorOffset="-15">
        <x-menu.button>
            <x-icons.user class="w-5 h-5" />
        </x-menu.button>

        <x-menu.items>
            <div class="px-3">
                {{ auth()->user()->name }}
            </div>

            <x-menu.itemDivider></x-menu.itemDivider>

            <x-modal>
                <x-menu.close>
                    <x-menu.item @click="modalOpen = true">
                        <x-icons.user class="w-4 h-4" />
                        {{ __('Profile') }}
                    </x-menu.item>
                </x-menu.close>

                <x-modal.panel title="Edit profile">
                    <x-form wire:submit="editProfile" class="flex flex-col gap-6">
                        <x-form.input name="profileForm.name" placeholder="{{ __('Name') }}" type="text" required
                            autofocus />

                        <x-form.input name="profileForm.email" placeholder="{{ __('Email') }}" type="email"
                            required />

                        <div class="flex justify-end">
                            <x-form.submit label="{{ __('Edit') }}" target="edit" />
                        </div>
                    </x-form>
                </x-modal.panel>
            </x-modal>

            <x-modal>
                <x-menu.close>
                    <x-menu.item @click="modalOpen = true">
                        <x-icons.lockClosed class="w-4 h-4" />
                        {{ __('Password') }}
                    </x-menu.item>
                </x-menu.close>

                <x-modal.panel title="Edit password">
                    <x-form wire:submit="editPassword" class="flex flex-col gap-6">
                        <x-form.input name="passwordForm.current_password" placeholder="{{ __('Current password') }}"
                            type="password" required autofocus />

                        <x-form.input name="passwordForm.password" placeholder="{{ __('New password') }}"
                            type="password" required />

                        <x-form.input name="passwordForm.password_confirmation"
                            placeholder="{{ __('Confirm password') }}" type="password" required />

                        <div class="flex justify-end">
                            <x-form.submit label="{{ __('Edit') }}" target="edit" />
                        </div>
                    </x-form>
                </x-modal.panel>
            </x-modal>

            <x-menu.close>
                <x-menu.itemLink href="{{ route('vaults.index') }}">
                    <x-icons.circleStack class="w-4 h-4" />
                    {{ __('Vaults') }}
                </x-menu.itemLink>
            </x-menu.close>

            <x-menu.itemDivider></x-menu.itemDivider>

            <x-modal>
                <x-menu.close>
                    <x-menu.item @click="modalOpen = true">
                        <x-icons.questionMarkCircle class="w-4 h-4" />
                        {{ __('Help') }}
                    </x-menu.item>
                </x-menu.close>

                <x-modal.panel title="Help">
                    <div x-data="{
                        selected: 0,
                        isSelected(selection) { return this.selected == selection },
                        toggle(selection) { this.selected = this.selected != selection ? selection : 0 },
                    }">
                        <ul>
                            <li class="relative p-3 mb-3 last:mb-0 bg-light-base-200 dark:bg-base-950" x-data="{ index: 1 }">
                                <button type="button" class="w-full font-semibold text-left" @click="toggle(index)">
                                    <div class="flex items-center justify-between">
                                        <span>{{ __( 'General' ) }}</span>
                                        <x-icons.chevronRight x-show="!isSelected(index)" class="w-5 h-5" />
                                        <x-icons.chevronDown x-show="isSelected(index)" class="w-5 h-5" x-cloak />
                                    </div>
                                </button>
                                <div class="relative overflow-hidden transition-all duration-700" x-show="isSelected(index)" x-collapse>
                                    <div class="flex flex-col gap-3 pt-3">
                                        <p>
                                            <b>{{ __('Vaults') }}</b>:
                                            {{ __('Vaults are simply storage containers for your files, and Many Notes lets you choose to keep all your files in one vault or organize them into separate vaults.') }}
                                        </p>
                                        <p>
                                            <b>{{ __('Bookmarks') }}</b>:
                                            {{ __('Any file can be accessed via its unique URL, making it easy to bookmark specific files using your browser.') }}
                                        </p>
                                        <p>
                                            <b>{{ __('Collaboration') }}</b>:
                                            {{ __('Easily invite others to join your vaults and watch as everyone contributes their ideas in one place.') }}
                                        </p>
                                    </div>
                                </div>
                            </li>
                            <li class="relative p-3 mb-3 last:mb-0 bg-light-base-200 dark:bg-base-950" x-data="{ index: 2 }">
                                <button type="button" class="w-full font-semibold text-left" @click="toggle(index)">
                                    <div class="flex items-center justify-between">
                                        <span>{{ __( 'Importing and exporting' ) }}</span>
                                        <x-icons.chevronRight x-show="!isSelected(index)" class="w-5 h-5" />
                                        <x-icons.chevronDown x-show="isSelected(index)" class="w-5 h-5" x-cloak />
                                    </div>
                                </button>
                                <div class="relative overflow-hidden transition-all duration-700" x-show="isSelected(index)" x-collapse>
                                    <div class="flex flex-col gap-3 pt-3">
                                        <p>
                                            <b>{{ __('Importing vaults') }}</b>:
                                            {{ __('You can import any vault, not just those exported from Many Notes, although only files with valid extensions will be imported. To import a vault, simply upload a zip file containing your files and folders. You may upload text files (md, txt, pdf), images (jpg, jpeg, png, gif, webp), videos (mp4, avi), and audio files (mp3, flac).') }}
                                        </p>
                                        <p>
                                            <b>{{ __('Exporting vaults') }}</b>:
                                            {{ __('The export feature allows you to download a ZIP file containing all files and folders within the selected vault. This feature enables you to back up your vaults into a single, convenient file format.') }}
                                        </p>
                                    </div>
                                </div>
                            </li>
                            <li class="relative p-3 mb-3 last:mb-0 bg-light-base-200 dark:bg-base-950" x-data="{ index: 3 }">
                                <button type="button" class="w-full font-semibold text-left" @click="toggle(index)">
                                    <div class="flex items-center justify-between">
                                        <span>{{ __( 'Tree view' ) }}</span>
                                        <x-icons.chevronRight x-show="!isSelected(index)" class="w-5 h-5" />
                                        <x-icons.chevronDown x-show="isSelected(index)" class="w-5 h-5" x-cloak />
                                    </div>
                                </button>
                                <div class="relative overflow-hidden transition-all duration-700" x-show="isSelected(index)" x-collapse>
                                    <div class="flex flex-col gap-3 pt-3">
                                        <p>{{ __('The tree view panel has a context menu with different options to help you build and organize your vault.') }}</p>
                                        <p>{{ __('You can create multiple levels of folders, import files, select a template folder, and rename, move, or delete files and folders.') }}</p>
                                        <p>{{ __('To open the context menu, right-click on a file or folder if you\'re on a desktop, or long press it if you\'re on a mobile device.') }}</p>
                                    </div>
                                </div>
                            </li>
                            <li class="relative p-3 mb-3 last:mb-0 bg-light-base-200 dark:bg-base-950" x-data="{ index: 4 }">
                                <button type="button" class="w-full font-semibold text-left" @click="toggle(index)">
                                    <div class="flex items-center justify-between">
                                        <span>{{ __( 'Templates' ) }}</span>
                                        <x-icons.chevronRight x-show="!isSelected(index)" class="w-5 h-5" />
                                        <x-icons.chevronDown x-show="isSelected(index)" class="w-5 h-5" x-cloak />
                                    </div>
                                </button>
                                <div class="relative overflow-hidden transition-all duration-700" x-show="isSelected(index)" x-collapse>
                                    <div class="flex flex-col gap-3 pt-3">
                                        <p>{{ __('To use templates, first choose a folder in the tree view where you want to keep your template notes. Right-click on that folder and select "Template Folder" to mark it for your templates.') }}</p>
                                        <p>{!! __('Notes created in this special folder will automatically be seen as templates. You can add placeholders like @{{date}}, @{{time}} and @{{content}} in these notes, and they will be replaced with the correct information when you use the template.') !!}</p>
                                        <p>{{ __('To use a template in your notes, simply choose the Template option from the Insert menu in the Markdown editor and select one from the list. This will help you maintain consistent formatting in your notes.') }}</p>
                                    </div>
                                </div>
                            </li>
                        </ul>
                    </div>
                </x-modal.panel>
            </x-modal>

            <x-modal>
                <x-menu.close>
                    <x-menu.item @click="modalOpen = true">
                        <x-icons.informationCircle class="w-4 h-4" />
                        {{ __('About') }}
                    </x-menu.item>
                </x-menu.close>

                <x-modal.panel title="About">
                    <div class="flex flex-col gap-4">
                        <p>
                            {{ __('Follow the development, report any issues, and check for new versions on GitHub.') }}
                        </p>

                        <div>
                            <h2>{{ __('Version') }}</h2>
                            <p>{{ $appVersion }}</p>
                        </div>

                        <div>
                            <h2>{{ __('GitHub') }}</h2>
                            <p>
                                <a href="{{ $githubUrl }}" target="_blank"
                                    class="text-primary-400 dark:text-primary-500 hover:text-primary-300 dark:hover:text-primary-600">{{ $githubUrl }}</a>
                            </p>
                        </div>
                    </div>
                </x-modal.panel>
            </x-modal>

            <x-menu.item wire:click="logout">
                <x-icons.arrowRightStartOnRectangle class="w-4 h-4" />
                {{ __('Logout') }}
            </x-menu.item>
        </x-menu.items>
    </x-menu>
</div>
