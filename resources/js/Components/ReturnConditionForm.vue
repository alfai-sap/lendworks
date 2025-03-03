<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Textarea } from "@/components/ui/textarea";
import { 
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from "@/components/ui/select";

const props = defineProps({
    rental: Object,
});

const conditionForm = useForm({
    condition: '',
    damage_description: '',
    damage_photos: [],
});

const conditions = [
    { value: 'excellent', label: 'Excellent - Like new condition' },
    { value: 'good', label: 'Good - Minor wear and tear' },
    { value: 'fair', label: 'Fair - Noticeable wear but functional' },
    { value: 'damaged', label: 'Damaged - Requires repair' }
];

const emit = defineEmits(['submit']);

const handleSubmit = () => {
    conditionForm.post(route('rentals.assess-condition', props.rental.id), {
        preserveScroll: true,
        onSuccess: () => {
            emit('submit');
        },
    });
};
</script>

<template>
    <form @submit.prevent="handleSubmit" class="space-y-4">
        <div class="space-y-2">
            <label class="text-sm font-medium">Item Condition</label>
            <Select v-model="conditionForm.condition">
                <SelectTrigger>
                    <SelectValue placeholder="Select condition..." />
                </SelectTrigger>
                <SelectContent>
                    <SelectItem 
                        v-for="condition in conditions" 
                        :key="condition.value"
                        :value="condition.value"
                    >
                        {{ condition.label }}
                    </SelectItem>
                </SelectContent>
            </Select>
        </div>

        <div class="space-y-2">
            <label class="text-sm font-medium">Notes</label>
            <Textarea
                v-model="conditionForm.damage_description"
                placeholder="Describe any damage or issues..."
                :required="conditionForm.condition === 'damaged'"
            />
        </div>

        <div class="flex justify-end gap-2">
            <Button type="submit" :disabled="!conditionForm.condition">
                Submit Assessment
            </Button>
        </div>
    </form>
</template>
