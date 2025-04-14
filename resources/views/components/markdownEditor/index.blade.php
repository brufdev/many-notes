<div class="flex flex-col h-full gap-3 overflow-hidden" x-data="toolbar"
    @mde-link.window="link($event.detail.name, $event.detail.path); $nextTick(() => { editor.focus() });"
    @mde-image.window="image($event.detail.name, $event.detail.path); $nextTick(() => { editor.focus() });"
    {{ $attributes }}
>
    <x-markdownEditor.toolbar x-show="!isSmallDevice()" x-cloak />
    <textarea class="w-full h-full p-0 px-1 bg-transparent border-0 focus:ring-0 focus:outline-0"
        id="noteEdit" x-show="isEditMode" wire:model.live.debounce.500ms="nodeForm.content"
        @keyup.enter="newLine"
    ></textarea>
    <div class="flex flex-col flex-grow pr-1 overflow-y-auto markdown-body" id="noteView" x-show="!isEditMode" x-html="html"></div>
    <x-markdownEditor.toolbar x-show="isSmallDevice()" x-cloak />
</div>

@script
    <script>
        Alpine.data('toolbar', () => ({
            editor: document.getElementById('noteEdit'),

            getSelection() {
                return this.editor.value.substring(this.editor.selectionStart, this.editor.selectionEnd);
            },

            parseSelection() {
                const selection = this.getSelection();

                if (selection.length === 0) {
                    return;
                }

                // Remove leading whitespaces
                this.editor.selectionStart += selection.length - selection.trimStart().length;

                // Remove trailing whitespaces
                this.editor.selectionEnd -= selection.length - selection.trimEnd().length;
            },

            changeSelection(text, moveSelectionStart, moveSelectionEnd = null) {
                const { selectionStart, selectionEnd } = this.editor;
                moveSelectionEnd = moveSelectionEnd === null
                    ? selectionEnd + moveSelectionStart
                    : selectionStart + moveSelectionEnd;
                this.editor.setRangeText(text);
                this.editor.focus();
                this.editor.setSelectionRange(selectionStart + moveSelectionStart, moveSelectionEnd);
                this.editor.dispatchEvent(new Event('input'));
            },

            setRangeText(replacement, startSelection = null, endSelection = null, selectMode = "preserve") {
                this.editor.setRangeText(replacement, startSelection, endSelection, selectMode);
                this.editor.focus();
                this.editor.dispatchEvent(new Event('input'));
            },

            setSelectionRange(selectionStart, selectionEnd, selectionDirection = "none") {
                this.editor.setSelectionRange(selectionStart, selectionEnd, selectionDirection);
            },

            parseLine(indexStart, indexEnd) {
                const lineStart = this.editor.value.substring(0, indexStart).lastIndexOf("\n") + 1;
                const lineEnd = this.editor.value.substring(indexStart).indexOf("\n") > -1
                    ? this.editor.value.substring(indexStart).indexOf("\n") + indexStart
                    : this.editor.value.length;
                const lineText = this.editor.value.substring(lineStart, lineEnd);
                const selectionStart = indexStart;
                const selectionEnd = indexEnd < lineEnd ? indexEnd : lineEnd;
                const selectionText = this.editor.value.substring(selectionStart, selectionEnd);
                let fullSelectionStart = selectionStart;
                if (selectionText.slice(0, 1) != " ") {
                    fullSelectionStart = this.editor.value.substring(lineStart, selectionStart)
                        .lastIndexOf(" ") + lineStart + 1;
                }
                let fullSelectionEnd = selectionEnd;
                if (selectionText.slice(-1) != " ") {
                    let aux = this.editor.value.substring(selectionEnd, lineEnd).indexOf(" ");
                    fullSelectionEnd = aux < 0 ? lineEnd : selectionEnd + aux;
                }
                const fullSelectionText = this.editor.value.substring(fullSelectionStart, fullSelectionEnd);

                return {
                    'lineStart': lineStart,
                    'lineEnd': lineEnd,
                    'lineText': lineText,
                    'selectionStart': selectionStart,
                    'selectionEnd': selectionEnd,
                    'selectionText': selectionText,
                    'selectionPrefixText': selectionStart - fullSelectionStart > 0
                        ? fullSelectionText.slice(0, selectionStart - fullSelectionStart)
                        : '',
                    'selectionSuffixText': selectionEnd - fullSelectionEnd < 0
                        ? fullSelectionText.slice(selectionEnd - fullSelectionEnd)
                        : '',
                    'fullSelectionStart': fullSelectionStart,
                    'fullSelectionEnd': fullSelectionEnd,
                    'fullSelectionText': fullSelectionText,
                    'originalSelectionStart': indexStart,
                    'originalSelectionEnd': indexEnd,
                };
            },

            parseAllLines(indexStart, indexEnd) {
                const lines = [];

                // Parse selected lines
                let pieces = this.editor.value.substring(indexStart, indexEnd).split(/\r?\n/);
                let indexInc = indexStart;
                for (i in pieces) {
                    lines.push({
                        'lineStart': indexInc,
                        'lineText': pieces[i],
                    });
                    indexInc += pieces[i].length + 1;
                }

                // Parse rest of last selected line
                pieces = this.editor.value.substring(indexEnd).split(/\r?\n/);
                lines[lines.length - 1].lineText += pieces.length
                    ? pieces[0]
                    : this.editor.value.substring(indexEnd);

                // Parse rest of first selected line
                pieces = this.editor.value.substring(0, indexStart).split(/\r?\n/);
                lines[0].lineStart = pieces.length ? indexStart - pieces[pieces.length - 1].length : 0;
                lines[0].lineText = pieces.length
                    ? pieces[pieces.length - 1] + lines[0].lineText
                    : this.editor.value.substring(0, indexEnd) + lines[0].lineText;

                return lines;
            },

            deleteStyles() {
                const parsed = this.parseLine(this.editor.selectionStart, this.editor.selectionEnd);

                if (this.hasTaskList(parsed.lineText)) {
                    this.setRangeText("", parsed.lineStart, parsed.lineStart + 6);
                    this.setSelectionRange(parsed.originalSelectionStart - 6, parsed.originalSelectionEnd - 6);
                    this.deleteStyles();
                    return;
                }

                if (this.hasUnorderedList(parsed.lineText)) {
                    this.setRangeText("", parsed.lineStart, parsed.lineStart + 2);
                    this.setSelectionRange(parsed.originalSelectionStart - 2, parsed.originalSelectionEnd - 2);
                    this.deleteStyles();
                    return;
                }

                if (this.hasOrderedList(parsed.lineText)) {
                    const numberLength = (this.getOrderedList(parsed.lineText)).toString().length;
                    this.setRangeText("", parsed.lineStart, parsed.lineStart + numberLength + 2);
                    this.setSelectionRange(
                        parsed.originalSelectionStart - numberLength - 2,
                        parsed.originalSelectionEnd - numberLength - 2,
                    );
                    this.deleteStyles();
                    return;
                }

                if (this.hasHeading(parsed.lineText)) {
                    const headingLength = this.getHeading(parsed.lineText).length;
                    this.setRangeText("", parsed.lineStart, parsed.lineStart + headingLength);
                    this.setSelectionRange(
                        parsed.originalSelectionStart - headingLength,
                        parsed.originalSelectionEnd - headingLength,
                    );
                    this.deleteStyles();
                    return;
                }

                if (this.hasBlockquote(parsed.lineText)) {
                    this.setRangeText("", parsed.lineStart, parsed.lineStart + 2);
                    this.setSelectionRange(parsed.originalSelectionStart - 2, parsed.originalSelectionEnd - 2);
                    this.deleteStyles();
                    return;
                }
            },

            newLine() {
                const parsed = this.parseLine(this.editor.selectionStart - 1, this.editor.selectionEnd - 1);

                if (this.hasTaskList(parsed.lineText)) {
                    this.addTaskList(this.editor.selectionStart);
                    return;
                }

                if (this.hasUnorderedList(parsed.lineText)) {
                    this.addUnorderedList(this.editor.selectionStart);
                    return;
                }

                if (this.hasOrderedList(parsed.lineText)) {
                    const number = this.getOrderedList(parsed.lineText) + 1;
                    this.addOrderedList(this.editor.selectionStart, number);
                    return;
                }
            },

            unorderedList() {
                const parsed = this.parseAllLines(this.editor.selectionStart, this.editor.selectionEnd);
                const everyLineUnorderedList = parsed.every(element => this.hasUnorderedList(element.lineText));
                let selectionStart, selectionEnd, addedTextLength = 0;

                for (i in parsed) {
                    ({ selectionStart, selectionEnd } = this.editor);

                    if (everyLineUnorderedList) {
                        this.setRangeText(
                            "",
                            parsed[i].lineStart + addedTextLength, parsed[i].lineStart + addedTextLength + 2
                        );
                        this.setSelectionRange(
                            parsed[i].lineStart < selectionStart
                                ? selectionStart - 2
                                : selectionStart, selectionEnd - 2,
                            );
                        addedTextLength -= 2;
                        continue;
                    }

                    if (!this.hasUnorderedList(parsed[i].lineText)) {
                        const text = "- ";
                        this.setRangeText(
                            text,
                            parsed[i].lineStart + addedTextLength,
                            parsed[i].lineStart + addedTextLength,
                        );
                        this.setSelectionRange(
                            parsed[i].lineStart < selectionStart
                                ? selectionStart + text.length
                                : selectionStart, selectionEnd + text.length,
                            );
                        addedTextLength += text.length;
                    }
                }
            },

            hasUnorderedList(text) {
                return !this.hasTaskList(text) && /^- /.test(text);
            },

            addUnorderedList(index) {
                const { selectionStart, selectionEnd } = this.editor;
                const text = "- ";
                this.setRangeText(text, index, index);
                this.setSelectionRange(selectionStart + text.length, selectionEnd + text.length);
            },

            orderedList() {
                const parsed = this.parseAllLines(this.editor.selectionStart, this.editor.selectionEnd);
                const everyLineOrderedList = parsed.every(element => this.hasOrderedList(element.lineText));
                let selectionStart, selectionEnd, numberLength, addedTextLength = 0,
                    number = 1;

                // Find if previous line has number list and get number
                if (!everyLineOrderedList && parsed[0].lineStart) {
                    const prevLineParsed = this.parseLine(parsed[0].lineStart - 1, parsed[0].lineStart - 1);

                    if (this.hasOrderedList(prevLineParsed.lineText)) {
                        number = this.getOrderedList(prevLineParsed.lineText) + 1;
                    }
                }

                for (i in parsed) {
                    ({ selectionStart, selectionEnd } = this.editor);

                    if (everyLineOrderedList) {
                        numberLength = (this.getOrderedList(parsed[i].lineText)).toString().length;
                        this.setRangeText(
                            "",
                            parsed[i].lineStart + addedTextLength,
                            parsed[i].lineStart + addedTextLength + numberLength + 2,
                        );
                        this.setSelectionRange(
                            parsed[i].lineStart < selectionStart
                                ? selectionStart - numberLength - 2
                                : selectionStart,
                            selectionEnd - numberLength - 2,
                        );
                        addedTextLength -= numberLength + 2;
                        continue;
                    }

                    if (!this.hasOrderedList(parsed[i].lineText)) {
                        const text = `${number}. `;
                        this.setRangeText(
                            text,
                            parsed[i].lineStart + addedTextLength,
                            parsed[i].lineStart + addedTextLength,
                        );
                        this.setSelectionRange(
                            parsed[i].lineStart < selectionStart
                                ? selectionStart + text.length
                                : selectionStart,
                            selectionEnd + text.length,
                        );
                        addedTextLength += text.length;
                    } else if (this.getOrderedList(parsed[i].lineText) != number) {
                        numberLength = (this.getOrderedList(parsed[i].lineText)).toString().length;
                        this.setRangeText(
                            number,
                            parsed[i].lineStart + addedTextLength,
                            parsed[i].lineStart + addedTextLength + numberLength,
                        );
                        this.setSelectionRange(
                            parsed[i].lineStart < selectionStart
                                ? selectionStart - (numberLength - (number).toString().length)
                                : selectionStart,
                            selectionEnd - (numberLength - (number).toString().length),
                        );
                        addedTextLength -= (numberLength - (number).toString().length);
                    }

                    number++;
                }
            },

            hasOrderedList(text) {
                return text.match(/^[\d]+\. /);
            },

            getOrderedList(text) {
                return parseInt(text.match(/\d+/)[0]);
            },

            addOrderedList(index, number) {
                const { selectionStart, selectionEnd } = this.editor;
                const text = `${number}. `;
                this.setRangeText(text, index, index);
                this.setSelectionRange(selectionStart + text.length, selectionEnd + text.length);
            },

            taskList() {
                const parsed = this.parseAllLines(this.editor.selectionStart, this.editor.selectionEnd);
                const everyLineTaskList = parsed.every(element => this.hasTaskList(element.lineText));
                let selectionStart, selectionEnd, addedTextLength = 0;

                for (i in parsed) {
                    ({ selectionStart, selectionEnd } = this.editor);

                    if (everyLineTaskList) {
                        this.setRangeText(
                            "",
                            parsed[i].lineStart + addedTextLength,
                            parsed[i].lineStart + addedTextLength + 6,
                        );
                        this.setSelectionRange(
                            parsed[i].lineStart < selectionStart
                                ? selectionStart - 6
                                : selectionStart,
                            selectionEnd - 6,
                        );
                        addedTextLength -= 6;
                        continue;
                    }

                    if (!this.hasTaskList(parsed[i].lineText)) {
                        const text = "- [ ] ";
                        this.setRangeText(
                            text,
                            parsed[i].lineStart + addedTextLength,
                            parsed[i].lineStart + addedTextLength,
                        );
                        this.setSelectionRange(
                            parsed[i].lineStart < selectionStart
                                ? selectionStart + text.length
                                : selectionStart,
                            selectionEnd + text.length,
                        );
                        addedTextLength += text.length;
                    }
                }
            },

            hasTaskList(text) {
                return /^- \[.{1}\] /.test(text);
            },

            addTaskList(index) {
                const { selectionStart, selectionEnd } = this.editor;
                const text = "- [ ] ";
                this.setRangeText(text, index, index);
                this.setSelectionRange(selectionStart + text.length, selectionEnd + text.length);
            },

            heading(level) {
                level = parseInt(level);

                if (level < 1 || level > 6) {
                    this.editor.focus();
                    return;
                }

                const parsed = this.parseLine(this.editor.selectionStart, this.editor.selectionEnd);
                const text = "#".repeat(level) + " ";

                if (this.hasHeading(parsed.lineText)) {
                    const headingLength = this.getHeading(parsed.lineText).length;

                    if (headingLength == text.length) {
                        this.editor.focus();
                        return;
                    }
                }

                this.deleteStyles();
                const { selectionStart, selectionEnd } = this.editor;
                this.setRangeText(text, parsed.lineStart, parsed.lineStart);
                this.setSelectionRange(selectionStart + text.length, selectionEnd + text.length);
            },

            hasHeading(text) {
                return /^[#]{1,6} /.test(text);
            },

            getHeading(text) {
                return text.match(/^[#]{1,6} /)[0];
            },

            addHeading(index, level) {
                const { selectionStart, selectionEnd } = this.editor;
                const text = "#".repeat(level) + " ";
                this.setRangeText(text, index, index);
                this.setSelectionRange(selectionStart + text.length, selectionEnd + text.length);
            },

            blockquote() {
                const parsed = this.parseAllLines(this.editor.selectionStart, this.editor.selectionEnd);
                const everyLineBlockquot = parsed.every(element => this.hasBlockquote(element.lineText));
                let selectionStart, selectionEnd, addedTextLength = 0;

                for (i in parsed) {
                    selectionStart = this.editor.selectionStart;
                    selectionEnd = this.editor.selectionEnd;

                    if (everyLineBlockquot) {
                        this.setRangeText(
                            "",
                            parsed[i].lineStart + addedTextLength,
                            parsed[i].lineStart + addedTextLength + 2,
                        );
                        this.setSelectionRange(
                            parsed[i].lineStart < selectionStart
                                ? selectionStart - 2
                                : selectionStart,
                            selectionEnd - 2,
                        );
                        addedTextLength -= 2;
                        continue;
                    }

                    if (!this.hasBlockquote(parsed[i].lineText)) {
                        const text = "> ";
                        this.setRangeText(
                            text,
                            parsed[i].lineStart + addedTextLength,
                            parsed[i].lineStart + addedTextLength,
                        );
                        this.setSelectionRange(
                            parsed[i].lineStart < selectionStart
                                ? selectionStart + text.length
                                : selectionStart,
                            selectionEnd + text.length,
                        );
                        addedTextLength += 2;
                    }
                }
            },

            hasBlockquote(text) {
                return /^> /.test(text);
            },

            bold() {
                this.parseSelection();
                const parsed = this.parseLine(this.editor.selectionStart, this.editor.selectionEnd);

                // Find the positions of two consecutive '*' characters
                const prefixFound = parsed.selectionPrefixText.search(/[\*]{2}/);
                const suffixFound = parsed.selectionSuffixText.search(/[\*]{2}/);

                if (prefixFound != -1 && suffixFound != -1) {
                    this.setRangeText(
                        "",
                        parsed.fullSelectionStart + prefixFound,
                        parsed.fullSelectionStart + prefixFound + 2,
                    );
                    this.setRangeText(
                        "",
                        parsed.selectionEnd + suffixFound - 2,
                        parsed.selectionEnd + suffixFound,
                    );
                } else {
                    this.setRangeText("**", parsed.selectionStart, parsed.selectionStart);
                    this.setRangeText("**", parsed.selectionEnd + 2, parsed.selectionEnd + 2);
                    this.setSelectionRange(parsed.selectionStart + 2, parsed.selectionEnd + 2);
                }
            },

            italic() {
                this.parseSelection();
                const parsed = this.parseLine(this.editor.selectionStart, this.editor.selectionEnd);

                // Find the positions of non-consecutive '*' characters (ignore ** because it's for bold)
                const prefixSingleFound = parsed.selectionPrefixText.search(/(?<!\*)\*(?!\*)/);
                const suffixSingleFound = parsed.selectionSuffixText.search(/(?<!\*)\*(?!\*)/);

                // Find the positions of three consecutive '*' characters
                const prefixTripleFound = parsed.selectionPrefixText.search(/[\*]{3}/);
                const suffixTripleFound = parsed.selectionSuffixText.search(/[\*]{3}/);

                if (prefixSingleFound != -1 && suffixSingleFound != -1) {
                    this.setRangeText(
                        "",
                        parsed.fullSelectionStart + prefixSingleFound,
                        parsed.fullSelectionStart + prefixSingleFound + 1,
                    );
                    this.setRangeText(
                        "",
                        parsed.selectionEnd + suffixSingleFound - 1,
                        parsed.selectionEnd + suffixSingleFound,
                    );
                } else if (prefixTripleFound != -1 && suffixTripleFound != -1) {
                    this.setRangeText(
                        "",
                        parsed.fullSelectionStart + prefixTripleFound,
                        parsed.fullSelectionStart + prefixTripleFound + 1,
                    );
                    this.setRangeText(
                        "",
                        parsed.selectionEnd + suffixTripleFound - 1,
                        parsed.selectionEnd + suffixTripleFound,
                    );
                } else {
                    this.setRangeText("*", parsed.selectionStart, parsed.selectionStart);
                    this.setRangeText("*", parsed.selectionEnd + 1, parsed.selectionEnd + 1);
                    this.setSelectionRange(parsed.selectionStart + 1, parsed.selectionEnd + 1);
                }
            },

            strikethrough() {
                this.parseSelection();
                const parsed = this.parseLine(this.editor.selectionStart, this.editor.selectionEnd);

                // Find the positions of two consecutive '~' characters
                const prefixFound = parsed.selectionPrefixText.search(/[~]{2}/);
                const suffixFound = parsed.selectionSuffixText.search(/[~]{2}/);

                if (prefixFound != -1 && suffixFound != -1) {
                    this.setRangeText(
                        "",
                        parsed.fullSelectionStart + prefixFound,
                        parsed.fullSelectionStart + prefixFound + 2,
                    );
                    this.setRangeText(
                        "",
                        parsed.selectionEnd + suffixFound - 2,
                        parsed.selectionEnd + suffixFound,
                    );
                } else {
                    this.setRangeText("~~", parsed.selectionStart, parsed.selectionStart);
                    this.setRangeText("~~", parsed.selectionEnd + 2, parsed.selectionEnd + 2);
                    this.setSelectionRange(parsed.selectionStart + 2, parsed.selectionEnd + 2);
                }
            },

            link(name = '', url = '') {
                this.parseSelection();
                const { selectionStart, selectionEnd } = this.editor;
                let alt = this.editor.value.substring(selectionStart, selectionEnd);

                if (alt.length === 0) {
                    alt = name;
                }

                const text = `[${alt}](${url})`;
                let moveSelection = !alt.length ? 1 : alt.length + 3;

                if (alt.length && url.length) {
                    moveSelection += url.length + 1;
                }

                this.setRangeText(text, selectionStart, selectionEnd);
                this.setSelectionRange(selectionStart + moveSelection, selectionStart + moveSelection);
            },

            image(name = '', url = '') {
                this.parseSelection();
                const { selectionStart, selectionEnd } = this.editor;
                let alt = this.editor.value.substring(selectionStart, selectionEnd);

                if (alt.length === 0) {
                    alt = name;
                }

                const text = `![${alt}](${url})`;
                let moveSelection = !alt.length ? 2 : alt.length + 4;

                if (alt.length && url.length) {
                    moveSelection += url.length + 1;
                }

                this.setRangeText(text, selectionStart, selectionEnd);
                this.setSelectionRange(selectionStart + moveSelection, selectionStart + moveSelection);
            },

            table() {
                const columns = parseInt(Math.abs(prompt('{{ __('Number of columns?') }}')));
                if (isNaN(columns) || columns == 0) {
                    return;
                }

                const rows = parseInt(Math.abs(prompt('{{ __('Number of rows?') }}')));
                if (isNaN(rows) || rows == 0) {
                    return;
                }

                const selectionEnd = this.editor.selectionEnd;
                let text = '';

                for (let i = 0; i < rows; i++) {
                    if (i) {
                        text += `{{ PHP_EOL }}`;
                    }

                    text += this.tableRow(columns, '  ');

                    if (!i) {
                        text += `{{ PHP_EOL }}` + this.tableRow(columns, '---');
                    }
                }

                this.setRangeText(text, selectionEnd, selectionEnd);
                this.setSelectionRange(selectionEnd + 2, selectionEnd + 2);
            },

            tableRow(columns, defaultText) {
                let text = '|';

                for (let i = 0; i < columns; i++) {
                    text += `${defaultText}|`;
                }

                return text;
            }
        }));
    </script>
@endscript
