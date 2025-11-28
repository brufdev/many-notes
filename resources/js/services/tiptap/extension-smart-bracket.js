import { Plugin, TextSelection } from '@tiptap/pm/state';
import { Extension } from '@tiptap/core';

const bracketPairs = {
    '<': '>',
    '«': '»',
    '(': ')',
    '[': ']',
    '{': '}',
    '"': '"',
    "'": "'",
};

export const SmartBracket = Extension.create({
    name: 'smartBracket',

    addProseMirrorPlugins() {
        return [
            new Plugin({
                props: {
                    handleTextInput: (view, from, to, text) => {
                        if (!(text in bracketPairs)) {
                            return false;
                        }

                        const { state, dispatch } = view;
                        const { tr, selection } = state;
                        const closing = bracketPairs[text];

                        // Empty selection
                        if (selection.empty) {
                            tr.insertText(text + closing, from, to);
                            tr.setSelection(TextSelection.create(tr.doc, from + 1));
                            dispatch(tr);

                            return true;
                        }

                        // Text selection
                        if (selection instanceof TextSelection) {
                            const { from: selFrom, to: selTo } = selection;
                            tr.insertText(text, selFrom, selFrom);
                            tr.insertText(closing, selTo + 1, selTo + 1);
                            tr.setSelection(TextSelection.create(tr.doc, selFrom + 1, selTo + 1));
                            dispatch(tr);

                            return true;
                        }

                        // All selection or non-text selection
                        tr.insertText(text + closing, from, to);

                        // Calculate cursor position
                        const mappedFrom = tr.mapping.map(from);
                        const mapped = tr.doc.resolve(mappedFrom);
                        const finalPos = mapped.parent.isTextblock
                            ? mappedFrom + 1
                            : mappedFrom + 2;
                        tr.setSelection(TextSelection.create(tr.doc, finalPos));
                        dispatch(tr);

                        return true;
                    },
                    handleKeyDown(view, event) {
                        const { state, dispatch } = view;
                        const { selection } = state;

                        if (!selection.empty) {
                            return false;
                        }

                        const { $from } = selection;
                        const next = $from.nodeAfter;
                        const prev = $from.nodeBefore;

                        // Skip over closing chars
                        if (Object.values(bracketPairs).includes(event.key)) {
                            const nextChar = next?.text?.[0];

                            if (nextChar === event.key) {
                                dispatch(
                                    state.tr.setSelection(
                                        TextSelection.create(state.doc, $from.pos + 1)
                                    )
                                );
                                return true;
                            }

                            return false;
                        }

                        // Delete bracket pair when cursor between opening and closing chars
                        if (event.key === 'Backspace') {
                            if (
                                prev &&
                                next &&
                                prev.isText &&
                                next.isText &&
                                $from.parent.isTextblock
                            ) {
                                const prevChar = prev.text.slice(-1);
                                const nextChar = next.text[0];

                                if (bracketPairs[prevChar] === nextChar) {
                                    dispatch(state.tr.delete($from.pos - 1, $from.pos + 1));
                                    return true;
                                }
                            }

                            return false;
                        }

                        return false;
                    }
                },
            }),
        ];
    },
});
