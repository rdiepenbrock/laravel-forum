<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import Container from "@/Components/Container.vue";
import Pagination from "@/Components/Pagination.vue";
import Comment from "@/Components/Comment.vue";
import InputLabel from "@/Components/InputLabel.vue";
import TextInput from "@/Components/TextInput.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";

import {computed, ref} from "vue";
import {router, useForm} from "@inertiajs/vue3";

import {relativeDate} from "@/Utilities/date";
import TextArea from "@/Components/TextArea.vue";
import InputError from "@/Components/InputError.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import {useConfirm} from "@/Utilities/Composables/useConfirm";
import MarkdownEditor from "@/Components/MarkdownEditor.vue";
import PageHeading from "@/Components/PageHeading.vue";
import Pill from "@/Components/Pill.vue";

const props = defineProps(['post', 'comments']);

const formatedDate = computed(() => relativeDate(props.post.created_at));

const commentForm = useForm({
    body: '',
});

const commentTextAreaRef = ref(null);
const commentIDBeingEdited = ref(null);
const commentBeingEdited = computed(() => props.comments.data.find(comment => comment.id === commentIDBeingEdited.value));
const { confirmation } = useConfirm();

const editComment = (commentId) => {
    commentIDBeingEdited.value = commentId;
    commentForm.body = commentBeingEdited.value?.body;
    commentTextAreaRef.value?.focus();
};

const cancelEditComment = () => {
  commentIDBeingEdited.value = null;
  commentForm.reset();
};

const addComment = () => commentForm.post(route('posts.comments.store', props.post.id), {
    preserveScroll: true,
    onSuccess: () => commentForm.reset(),
});

const updateComment = async () => {
    if (! await confirmation('Are you sure you want to update this comment?')) {
        commentTextAreaRef.value?.focus();
        return;
    }

    commentForm.put(route('comments.update', {
        comment: commentIDBeingEdited.value,
        page: props.comments.meta.current_page,
    }), {
        preserveScroll: true,
        onSuccess: cancelEditComment,
    });
};

const deleteComment = async (commentId) => {
    if (! await confirmation("Are you sure you want to delete this comment?")) {
        return;
    }

    router.delete(route('comments.destroy', { comment: commentId, page: props.comments.meta.current_page }), {
        preserveScroll: true,
    });
};
</script>

<template>
    <AppLayout :title="post.title">
        <Container>
            <Pill :href="route('posts.index', { topic: post.topic.slug })">
                {{ post.topic.name }}
            </Pill>
            <PageHeading class="mt-2">{{  post.title }}</PageHeading>

            <span class="block mt-1 text-sm text-gray-600">Published {{ formatedDate }} ago by {{ post.user.name }}</span>

            <article class="mt-6 prose prose-sm max-w-none" v-html="post.html"></article>

            <div class="mt-12">
                <h2 class="text-xl font-semibold">Comments</h2>

                <form v-if="$page.props.auth.user"
                      @submit.prevent="() => commentIDBeingEdited ? updateComment() : addComment()"
                      class="mt-4">
                    <div>
                        <InputLabel for="body"
                                    class="sr-only">
                            Comment
                        </InputLabel>
                        <MarkdownEditor id="body"
                                        ref="commentTextAreaRef"
                                        v-model="commentForm.body"
                                        placeholder="add comment"
                                        editorClass="min-h-[160px]"
                        />

                        <InputError :message="commentForm.errors.body" class="mt-1" />
                    </div>

                    <PrimaryButton type="submit"
                                   class="mt-3"
                                   :disabled="commentForm.processing"
                                   v-text="commentIDBeingEdited ? 'Update Comment' : 'Add Comment'"
                    ></PrimaryButton>
                    <SecondaryButton v-if="commentIDBeingEdited"
                                     @click="cancelEditComment"
                                     class="ml-2"
                    >Cancel</SecondaryButton>
                </form>

                <ul class="divide-y mt-4">
                    <li v-for="comment in comments.data"
                        :key="comment.id"
                        class="px-2 py-4"
                    >
                        <Comment @edit="editComment" @delete="deleteComment" :comment="comment" />
                    </li>
                </ul>

                <Pagination :meta="comments.meta" :only="['comments']" />
            </div>
        </Container>
    </AppLayout>
</template>

<style scoped>

</style>
