<script setup>
import { ref, watch } from "vue";
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
    DialogDescription,
} from "@/components/ui/dialog";
import { Button } from "@/components/ui/button";
import { useForm } from "@inertiajs/vue3";
import { Input } from "@/components/ui/input";
import { Textarea } from "@/components/ui/textarea";
import ImageUpload from "@/Components/ImageUpload.vue";
import PickupScheduleSelector from "@/Components/PickupScheduleSelector.vue";
import ReturnConditionForm from '@/Components/ReturnConditionForm.vue';

const props = defineProps({
    show: Boolean,
    rental: Object,
    type: {
        type: String,
        validator: (value) => ['schedule', 'proof'].includes(value)
    },
    lenderSchedules: Array,
    includeCondition: {
        type: Boolean,
        default: false
    }
});

const emit = defineEmits(['update:show']);

// Form for schedule submission
const scheduleForm = useForm({
    schedule_id: '',
    return_date: '',
});

// Form for proof submission
const proofForm = useForm({
    proof_image: null,
    notes: '',
});

const handleClose = () => {
    emit('update:show', false);
    scheduleForm.reset();
    proofForm.reset();
};

const handleScheduleSubmit = () => {
    scheduleForm.post(route('rental.return.schedule', props.rental.id), {
        preserveScroll: true,
        onSuccess: () => handleClose(),
    });
};

const handleProofSubmit = () => {
    proofForm.post(route('rental.return.proof', props.rental.id), {
        preserveScroll: true,
        onSuccess: () => handleClose(),
    });
};

const handleConditionSubmit = () => {
    handleClose();
};

watch(() => props.show, (newVal) => {
    if (!newVal) {
        scheduleForm.reset();
        proofForm.reset();
    }
});
</script>

<template>
    <Dialog :open="show" @update:open="$emit('update:show', $event)">
        <DialogContent class="sm:max-w-[425px]">
            <DialogHeader>
                <DialogTitle>
                    {{ type === 'schedule' ? 'Schedule Return' : 'Submit Return Proof' }}
                </DialogTitle>
                <DialogDescription>
                    {{ 
                        type === 'schedule' 
                            ? 'Select a return schedule and date' 
                            : 'Submit proof of item return' 
                    }}
                </DialogDescription>
            </DialogHeader>

            <!-- Schedule Return Form -->
            <form @submit.prevent="handleScheduleSubmit" v-if="type === 'schedule'">
                <div class="space-y-4">
                    <PickupScheduleSelector
                        v-model:selectedSchedule="scheduleForm.schedule_id"
                        v-model:selectedDate="scheduleForm.return_date"
                        :schedules="lenderSchedules"
                    />
                    
                    <div class="flex justify-end gap-3">
                        <Button type="button" variant="outline" @click="handleClose">
                            Cancel
                        </Button>
                        <Button 
                            type="submit"
                            :disabled="scheduleForm.processing || !scheduleForm.schedule_id || !scheduleForm.return_date"
                        >
                            Schedule Return
                        </Button>
                    </div>
                </div>
            </form>

            <!-- Condition Assessment Form -->
            <ReturnConditionForm
                v-if="includeCondition && type === 'proof'"
                :rental="rental"
                @submit="handleConditionSubmit"
            />

            <!-- Return Proof Form -->
            <form @submit.prevent="handleProofSubmit" v-else>
                <div class="space-y-4">
                    <ImageUpload
                        v-model="proofForm.proof_image"
                        label="Return Proof Image"
                        :error="proofForm.errors.proof_image"
                    />

                    <div class="space-y-2">
                        <label for="notes" class="text-sm font-medium">Notes</label>
                        <Textarea
                            id="notes"
                            v-model="proofForm.notes"
                            placeholder="Add any notes about the item's condition..."
                            :error="proofForm.errors.notes"
                        />
                    </div>

                    <div class="flex justify-end gap-3">
                        <Button type="button" variant="outline" @click="handleClose">
                            Cancel
                        </Button>
                        <Button 
                            type="submit"
                            :disabled="proofForm.processing || !proofForm.proof_image"
                        >
                            Submit Proof
                        </Button>
                    </div>
                </div>
            </form>
        </DialogContent>
    </Dialog>
</template>
