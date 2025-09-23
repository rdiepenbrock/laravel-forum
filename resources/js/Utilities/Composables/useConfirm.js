import {reactive, readonly} from "vue";

const globalState = reactive({
    show: false,
    title: '',
    message: '',
    resolver: null,
});

export function useConfirm() {
    const resetModal = () => {
        globalState.title = '';
        globalState.message = '';
        globalState.show = false;
        globalState.resolver = null;
    };

    return {
        state: readonly(globalState),
        confirmation: (title = "Please Confirm", message) => {
            globalState.show = true;
            globalState.title = title;
            globalState.message = "Are you sure you want to continue?"

            return new Promise((resolver) => {
                globalState.resolver = resolver;
            });
        },
        confirm: () => {
            if (globalState.resolver) {
                globalState.resolver(true);
            }

            resetModal();
        },
        cancel: () => {
            if (globalState.resolver) {
                globalState.resolver(false);
            }

            resetModal();
        }
    };
}
