<!-- resources/js/Components/CancellationDialog.vue -->
<script setup>
import { ref } from 'vue';
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
    DialogDescription,
    DialogFooter,
} from "@/components/ui/dialog";
import { Button } from "@/components/ui/button";
import { Label } from "@/components/ui/label";
import { RadioGroup, RadioGroupItem } from "@/components/ui/radio-group";
import { Textarea } from "@/components/ui/textarea";
import { Alert, AlertDescription } from "@/components/ui/alert";
import { AlertTriangle } from "lucide-vue-next";
import SuccessDialog from './SuccessDialog.vue';

const props = defineProps({
    show: Boolean
});

const emits = defineEmits(['update:show', 'confirm']);

const selectedReason = ref('');
const description = ref('');
const showSuccessDialog = ref(false);

const handleConfirm = () => {
    emits('confirm', {
        reason: selectedReason.value,
        description: description.value
    });
    emits('update:show', false);
    showSuccessDialog.value = true;

    // Auto close success dialog after 2 seconds
    setTimeout(() => {
        showSuccessDialog.value = false;
    }, 2000);
};

const reasons = [
    { id: 'found-another', label: 'Found another item' },
    { id: 'not-needed', label: 'No longer needed the item' },
    { id: 'schedule-change', label: 'Change of schedule/plans' },
    { id: 'terms-unsuitable', label: 'Rental terms not suitable' },
    { id: 'others', label: 'Others (please specify)' },
];
</script>

<template>
    <Dialog :open="show" @update:open="$emit('update:show', $event)">
        <DialogContent class="sm:max-w-[425px]">
            <DialogHeader>
                <DialogTitle class="text-xl font-semibold mb-4">Rental Cancellation</DialogTitle>
                <DialogDescription>
                    Please provide details about your cancellation request.
                </DialogDescription>
            </DialogHeader>

            <div class="grid gap-4 py-4">
                <!-- Reason Selection -->
                <div class="space-y-4">
                    <Label class="text-base mb-5">Please Select</Label>
                    <RadioGroup v-model="selectedReason" class="gap-3 mb-4 mt-4">
                        <div v-for="reason in reasons" :key="reason.id"
                             class="flex items-center space-x-2 mb-5">
                            <RadioGroupItem :value="reason.id" />
                            <Label>{{ reason.label }}</Label>
                        </div>
                    </RadioGroup>
                </div>

                <!-- Description -->
                <div class="space-y-3 mb-4">
                    <Label class="text-base">Description</Label>
                    <Textarea
                        v-model="description"
                        placeholder="Please provide additional details about your cancellation..."
                        class="min-h-[100px]"
                    />
                </div>

                <!-- Disclaimer -->
                <Alert variant="destructive" class="bg-destructive/5 border-none">
                    <AlertTriangle class="h-4 w-4" />
                    <AlertDescription class="text-xs text-muted-foreground">
                        Please note that cancellation may be subject to our refund policy based on the timing of your request.
                        Refer to our cancellation terms for more details.
                    </AlertDescription>
                </Alert>
            </div>

            <DialogFooter>
                <Button
                    variant="outline"
                    @click="$emit('update:show', false)">
                    Cancel
                </Button>
                <Button
                    variant="destructive"
                    @click="handleConfirm"
                    :disabled="!selectedReason">
                    Confirm
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>

    <SuccessDialog v-model:show="showSuccessDialog" />
</template>
