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

                        if (selection.empty) {
                            tr.insertText(text + closing, from, to);
                            tr.setSelection(
                                state.selection.constructor.near(tr.doc.resolve(from + 1))
                            );
                        } else {
                            const { from: selFrom, to: selTo } = selection;
                            tr.insertText(text, selFrom, selFrom);
                            tr.insertText(closing, selTo + 1, selTo + 1);
                            tr.setSelection(state.selection.constructor.create(tr.doc, selFrom + 1, selTo + 1));
                        }

                        dispatch(tr);
                        return true;
                    },
                    handleKeyDown(view, event) {
                        const { state, dispatch } = view;
                        const { selection } = state;
                        const { $from } = selection;

                        // Skip over closing chars
                        if (selection.empty && Object.values(bracketPairs).includes(event.key)) {
                            const nextChar = $from.nodeAfter?.text?.[0];

                            if (nextChar === event.key) {
                                dispatch(
                                    state.tr.setSelection(
                                        TextSelection.create(state.doc, $from.pos + 1)
                                    )
                                );

                                return true;
                            }
                        }

                        // Delete bracket pair when cursor between opening and closing chars
                        if (event.key === 'Backspace' && selection.empty) {
                            const prevChar = $from.nodeBefore?.text?.slice(-1);
                            const nextChar = $from.nodeAfter?.text?.[0];

                            if (prevChar && bracketPairs[prevChar] === nextChar) {
                                dispatch(
                                    state.tr.delete($from.pos - 1, $from.pos + 1)
                                );

                                return true;
                            }
                        }

                        return false;
                    }
                },
            }),
        ];
    },
});
