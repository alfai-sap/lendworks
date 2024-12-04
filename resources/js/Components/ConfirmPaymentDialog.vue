<!-- resources/js/Components/ConfirmPaymentDialog.vue -->
<script setup>
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
    DialogDescription,
    DialogFooter,
} from "@/components/ui/dialog";
import { Button } from "@/components/ui/button";
import { CheckCircle2 } from "lucide-vue-next";

const props = defineProps({
    show: Boolean,
    totalAmount: {
        type: Number,
        required: true
    },
    paymentMethod: {
        type: String,
        required: true
    }
});

const emits = defineEmits(['update:show', 'confirm']);

const formatNumber = (value) => {
    return new Intl.NumberFormat("en-US").format(value);
};

const getMethodLabel = (method) => {
    const labels = {
        'gcash': 'GCash',
        'maya': 'Maya',
        'credit-card': 'Credit Card'
    };
    return labels[method] || method;
};
</script>

<template>
    <Dialog :open="show" @update:open="$emit('update:show', $event)">
        <DialogContent class="sm:max-w-[425px]">
            <DialogHeader>
                <div class="flex flex-col items-center text-center">
                    <CheckCircle2 class="w-12 h-12 text-primary mb-4"/>
                    <DialogTitle class="text-xl font-semibold">Confirm Payment</DialogTitle>
                    <DialogDescription class="mt-2">
                        You are about to pay ₱{{ formatNumber(totalAmount) }} via {{ getMethodLabel(paymentMethod) }}
                    </DialogDescription>
                </div>
            </DialogHeader>

            <DialogFooter class="mt-6">
                <div class="flex gap-3 w-full">
                    <Button 
                        variant="outline" 
                        class="flex-1"
                        @click="$emit('update:show', false)"
                    >
                        Cancel
                    </Button>
                    <Button 
                        class="flex-1"
                        @click="$emit('confirm')"
                    >
                        Confirm Payment
                    </Button>
                </div>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>