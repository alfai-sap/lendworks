<!-- resources/js/Components/SuccessDialog.vue -->
<script setup>
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
    DialogDescription,
} from "@/components/ui/dialog";
import { CheckCircle2 } from "lucide-vue-next";

defineProps({
    show: Boolean
});

defineEmits(['update:show']);
</script>

<template>
    <Dialog :open="show" @update:open="$emit('update:show', $event)">
        <DialogContent class="sm:max-w-[425px]">
            <DialogHeader>
                <div class="flex flex-col items-center text-center">
                    <CheckCircle2 class="w-12 h-12 text-primary mb-4"/>
                    <DialogTitle class="text-xl font-semibold">Cancellation Successful</DialogTitle>
                    <DialogDescription class="mt-2">
                        Your rental cancellation request has been submitted successfully.
                    </DialogDescription>
                </div>
            </DialogHeader>
        </DialogContent>
    </Dialog>
</template>
