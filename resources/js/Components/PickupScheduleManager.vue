<script setup>
import { ref, watch, computed } from 'vue';
import { useForm } from '@inertiajs/vue3';
// Fix import paths to use capital 'C' in Components
import { Button } from "@/Components/ui/button";
import { Input } from "@/Components/ui/input";
import { Textarea } from "@/Components/ui/textarea";
import { formatDateTime } from "@/lib/formatters";
import { Trash2Icon, PencilIcon, CalendarIcon } from "lucide-vue-next";
import { Popover, PopoverContent, PopoverTrigger } from "@/Components/ui/popover";
import { Calendar } from "@/Components/ui/calendar"; // Updated import path
import { cn } from "@/lib/utils";
import { format } from "date-fns";

// Remove unused imports
// import { CalendarDate, DateFormatter, getLocalTimeZone } from "@internationalized/date";

const props = defineProps({
    rentalId: {
        type: Number,
        required: true
    },
    schedules: {
        type: Array,
        default: () => []
    }
});

const form = useForm({
    pickup_date: '',
    pickup_time: '',
    notes: '',
});

const editForm = useForm({
    id: null,
    pickup_date: '',
    pickup_time: '',
    notes: '',
});

const isEditing = ref(false);

// Add computed properties for form fields
const currentPickupDate = computed({
    get: () => isEditing.value ? editForm.pickup_date : form.pickup_date,
    set: (value) => {
        if (isEditing.value) {
            editForm.pickup_date = value;
        } else {
            form.pickup_date = value;
        }
    }
});

const currentPickupTime = computed({
    get: () => isEditing.value ? editForm.pickup_time : form.pickup_time,
    set: (value) => {
        if (isEditing.value) {
            editForm.pickup_time = value;
        } else {
            form.pickup_time = value;
        }
    }
});

const currentNotes = computed({
    get: () => isEditing.value ? editForm.notes : form.notes,
    set: (value) => {
        if (isEditing.value) {
            editForm.notes = value;
        } else {
            form.notes = value;
        }
    }
});

// Convert date string to Date object for the calendar
const stringToDate = (dateString) => {
    return dateString ? new Date(dateString) : null;
};

// Convert Date object to date string format
const dateToString = (date) => {
    return date ? format(date, 'yyyy-MM-dd') : '';
};

// Calendar state
const date = computed({
    get: () => stringToDate(isEditing.value ? editForm.pickup_date : form.pickup_date),
    set: (value) => {
        if (isEditing.value) {
            editForm.pickup_date = dateToString(value);
        } else {
            form.pickup_date = dateToString(value);
        }
    }
});

// Format date for display
const formattedDate = computed(() => {
    const selectedDate = date.value;
    if (!selectedDate) return "Pick a date";
    return format(selectedDate, 'MMMM d, yyyy');
});

// Minimum date (today)
const today = new Date();
today.setHours(0, 0, 0, 0);

const submitForm = () => {
    form.post(route('pickup-schedules.store', props.rentalId), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
        },
    });
};

const editSchedule = (schedule) => {
    editForm.id = schedule.id;
    editForm.pickup_date = schedule.pickup_date;
    editForm.pickup_time = schedule.pickup_time;
    editForm.notes = schedule.notes;
    isEditing.value = true;
};

const updateSchedule = () => {
    editForm.patch(route('pickup-schedules.update', editForm.id), {
        preserveScroll: true,
        onSuccess: () => {
            isEditing.value = false;
            editForm.reset();
        },
    });
};

const deleteSchedule = (id) => {
    if (confirm('Are you sure you want to delete this schedule?')) {
        useForm().delete(route('pickup-schedules.destroy', id), {
            preserveScroll: true,
        });
    }
};
</script>

<template>
    <div class="space-y-6">
        <!-- Add/Edit Form -->
        <div class="space-y-4 p-4 border rounded-lg">
            <h3 class="font-semibold">
                {{ isEditing ? 'Edit Pickup Schedule' : 'Add Pickup Schedule' }}
            </h3>
            <form @submit.prevent="isEditing ? updateSchedule() : submitForm()" class="space-y-4">
                <div class="grid sm:grid-cols-2 gap-4">
                    <!-- Date Picker -->
                    <div class="space-y-2">
                        <label class="text-sm font-medium">Date</label>
                        <Popover>
                            <PopoverTrigger as-child>
                                <Button
                                    variant="outline"
                                    :class="cn(
                                        'w-full justify-start text-left font-normal',
                                        !date && 'text-muted-foreground'
                                    )"
                                >
                                    <CalendarIcon class="w-4 h-4 mr-2" />
                                    {{ formattedDate }}
                                </Button>
                            </PopoverTrigger>
                            <PopoverContent class="w-auto p-0">
                                <Calendar
                                    v-model="date"
                                    :min-date="today"
                                    initialFocus
                                />
                            </PopoverContent>
                        </Popover>
                    </div>

                    <!-- Time Input -->
                    <div class="space-y-2">
                        <label class="text-sm font-medium">Time</label>
                        <Input
                            type="time"
                            v-model="currentPickupTime"
                            required
                            class="w-full"
                        />
                    </div>
                </div>
                
                <div class="space-y-2">
                    <label class="text-sm font-medium">Notes (Optional)</label>
                    <Textarea
                        v-model="currentNotes"
                        placeholder="Add any additional notes or instructions..."
                        rows="3"
                    />
                </div>

                <div class="flex gap-2">
                    <Button 
                        type="submit" 
                        :disabled="isEditing ? editForm.processing : form.processing"
                    >
                        {{ isEditing ? 'Update Schedule' : 'Add Schedule' }}
                    </Button>
                    <Button 
                        v-if="isEditing"
                        type="button"
                        variant="outline"
                        @click="isEditing = false; editForm.reset()"
                    >
                        Cancel
                    </Button>
                </div>
            </form>
        </div>

        <!-- Schedules List -->
        <div v-if="schedules.length > 0" class="space-y-4">
            <h3 class="font-semibold">Available Pickup Schedules</h3>
            <div class="space-y-3">
                <div v-for="schedule in schedules" :key="schedule.id" 
                    class="flex items-center justify-between p-4 border rounded-lg">
                    <div class="space-y-1">
                        <p class="font-medium">
                            {{ formatDateTime(schedule.pickup_date, 'MMMM D, YYYY') }}
                            at {{ formatDateTime(schedule.pickup_time, 'h:mm A') }}
                        </p>
                        <p v-if="schedule.notes" class="text-sm text-muted-foreground">
                            {{ schedule.notes }}
                        </p>
                    </div>
                    <div class="flex gap-2">
                        <Button 
                            variant="outline" 
                            size="icon"
                            @click="editSchedule(schedule)"
                        >
                            <PencilIcon class="h-4 w-4" />
                        </Button>
                        <Button 
                            variant="destructive" 
                            size="icon"
                            @click="deleteSchedule(schedule.id)"
                        >
                            <Trash2Icon class="h-4 w-4" />
                        </Button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
