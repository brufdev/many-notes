<script setup lang="ts">
import Accordion from '@/components/accordion/Accordion.vue';
import AccordionItem from '@/components/accordion/AccordionItem.vue';
</script>

<template>
    <Accordion>
        <AccordionItem>
            <template #header>General</template>
            <template #content>
                <div class="flex flex-col gap-3 pt-3">
                    <p>
                        <span class="font-semibold">Vaults</span>: Vaults are simply storage
                        containers for your files, and Many Notes lets you choose to keep all your
                        files in one vault or organize them into separate vaults.
                    </p>
                    <p>
                        <span class="font-semibold">Bookmarks</span>: Any file can be accessed via
                        its unique URL, making it easy to bookmark specific files using your
                        browser.
                    </p>
                    <p>
                        <span class="font-semibold">Collaboration</span>: Easily invite others to
                        join your vaults and watch as everyone contributes their ideas in one place.
                    </p>
                </div>
            </template>
        </AccordionItem>
        <AccordionItem>
            <template #header>Importing and exporting</template>
            <template #content>
                <div class="flex flex-col gap-3 pt-3">
                    <p>
                        <span class="font-semibold">Importing vaults</span>: You can import any
                        vault, not just those exported from Many Notes, although only files with
                        valid extensions will be imported. To import a vault, simply upload a zip
                        file containing your files and folders. You may upload text files (md, txt,
                        pdf), images (jpg, jpeg, png, gif, webp), videos (mp4, avi), and audio files
                        (mp3, flac).
                    </p>
                    <p>
                        <span class="font-semibold">Exporting vaults</span>: The export feature
                        allows you to download a ZIP file containing all files and folders within
                        the selected vault. This feature enables you to back up your vaults into a
                        single convenient file.
                    </p>
                </div>
            </template>
        </AccordionItem>
        <AccordionItem>
            <template #header>Tree view</template>
            <template #content>
                <div class="flex flex-col gap-3 pt-3">
                    <p>
                        The tree view panel has a context menu with different options to help you
                        build and organize your vault.
                    </p>
                    <p>
                        You can create multiple levels of folders, import files, select a template
                        folder, and rename, move, or delete files and folders.
                    </p>
                    <p>
                        To open the context menu, right-click on a file or folder if you're on a
                        desktop, or long press it if you're on a mobile device.
                    </p>
                </div>
            </template>
        </AccordionItem>
        <AccordionItem>
            <template #header>Templates</template>
            <template #content>
                <div class="flex flex-col gap-3 pt-3">
                    <p>
                        To use templates, first choose a folder in the tree view where you want to
                        keep your template notes. Right-click on that folder and select "Template
                        Folder" to mark it for your templates.
                    </p>
                    <p>
                        Notes created in this special folder will automatically be seen as
                        templates. You can add placeholders like <span v-pre>{{ date }}</span
                        >, <span v-pre>{{ time }}</span> and <span v-pre>{{ content }}</span> in
                        these notes, and they will be replaced with the correct information when you
                        use the template.
                    </p>
                    <p>
                        To use a template in your notes, simply choose the Template option from the
                        Insert menu in the Markdown editor and select one from the list. This will
                        help you maintain consistent formatting in your notes.
                    </p>
                </div>
            </template>
        </AccordionItem>
    </Accordion>
</template>
