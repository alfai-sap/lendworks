<!-- resources/js/Components/PaymentMethodDialog.vue -->
<script setup>
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
} from "@/components/ui/dialog";
import { Button } from "@/components/ui/button";
import { Label } from "@/components/ui/label";
import { RadioGroup, RadioGroupItem } from "@/components/ui/radio-group";
import { ChevronLeft } from "lucide-vue-next";
import { ref } from 'vue';
import ConfirmPaymentDialog from './ConfirmPaymentDialog.vue';

//confirmation
const showConfirmDialog = ref(false);

const handleConfirm = () => {
    showConfirmDialog.value = true;
};

const handleFinalConfirm = () => {
    showConfirmDialog.value = false;
    emits('confirm', { method: selectedMethod.value });
    emits('update:show', false);
};

const props = defineProps({
    show: Boolean,
    totalAmount: {
        type: Number,
        required: true
    }
});

const emits = defineEmits(['update:show', 'confirm']);

const selectedMethod = ref('');

const formatNumber = (value) => {
    return new Intl.NumberFormat("en-US").format(value);
};

const paymentMethods = {
    ewallet: [
        { 
            id: 'gcash',
            label: 'GCash',
            description: 'No GCash yet? Sign up now!'
        },
        { 
            id: 'maya',
            label: 'Maya',
            description: null
        }
    ],
    cards: [
        {
            id: 'credit-card',
            label: 'Credit Card',
            description: null
        }
    ]
};

</script>

<template>
    <Dialog :open="show" @update:open="$emit('update:show', $event)">
        <DialogContent class="sm:max-w-[425px]">
            <!-- Header with Back Button -->
            <DialogHeader class="flex flex-row items-center space-x-4">
                <Button 
                    variant="ghost" 
                    size="icon" 
                    @click="$emit('update:show', false)"
                    class="h-8 w-8"
                >
                    <ChevronLeft class="h-5 w-5" />
                </Button>
                <DialogTitle class="text-xl font-semibold">Select Payment Method</DialogTitle>
            </DialogHeader>

            <div class="mt-6 space-y-6">
                <!-- Payment Methods Selection -->
                <div class="space-y-4">
                    <Label class="text-base">How do you want to pay?</Label>

                    <!-- E-wallet Section -->
                    <div class="space-y-4">
                        <Label class="text-sm text-muted-foreground">E-wallet</Label>
                        <RadioGroup v-model="selectedMethod" class="space-y-3">
                            <div v-for="method in paymentMethods.ewallet" 
                                :key="method.id"
                                class="flex flex-col space-y-1 rounded-md border p-4"
                            >
                                <div class="flex items-center space-x-3">
                                    <RadioGroupItem :value="method.id" />
                                    <Label>{{ method.label }}</Label>
                                </div>
                                <p v-if="method.description" 
                                   class="text-xs text-blue-500 pl-8 hover:underline cursor-pointer">
                                    {{ method.description }}
                                </p>
                            </div>
                        </RadioGroup>
                    </div>

                    <!-- Credit Card Section -->
                    <div class="space-y-4">
                        <Label class="text-sm text-muted-foreground">Cards</Label>
                        <RadioGroup v-model="selectedMethod" class="space-y-3">
                            <div v-for="method in paymentMethods.cards" 
                                :key="method.id"
                                class="flex items-center space-x-3 rounded-md border p-4"
                            >
                                <RadioGroupItem :value="method.id" />
                                <Label>{{ method.label }}</Label>
                            </div>
                        </RadioGroup>
                    </div>
                </div>
            </div>

            <!-- Payment Button -->
            <div class="mt-6">
                <Button 
                    class="w-full" 
                    :disabled="!selectedMethod"
                    @click="handleConfirm"
                >
                    Pay amount of ₱{{ formatNumber(totalAmount) }}
                </Button>
            </div>
        </DialogContent>
    </Dialog>

    <ConfirmPaymentDialog
        v-model:show="showConfirmDialog"
        :total-amount="totalAmount"
        :payment-method="selectedMethod"
        @confirm="handleFinalConfirm"
    />
</template>