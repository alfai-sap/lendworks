<script setup>
import { ref } from 'vue';
import { ChevronLeft, ChevronRight } from 'lucide-vue-next';
import { Button } from '@/Components/ui/button';
import { cn } from '@/lib/utils';
import { addDays, format, addMonths, subMonths, isSameMonth, isSameDay, isToday as isDateToday } from 'date-fns';

const props = defineProps({
  modelValue: {
    type: Date,
    required: false,
  },
  minDate: {
    type: Date,
    required: false,
  },
  maxDate: {
    type: Date,
    required: false,
  },
  initialFocus: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits(['update:modelValue']);

const currentMonth = ref(props.modelValue || new Date());
const focusDate = ref(null);

const days = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];

const getWeeksInMonth = (date) => {
  const start = new Date(date.getFullYear(), date.getMonth(), 1);
  const end = new Date(date.getFullYear(), date.getMonth() + 1, 0);
  const weeks = [];
  let currentWeek = [];
  
  // Add empty days for the first week
  for (let i = 0; i < start.getDay(); i++) {
    currentWeek.push(null);
  }
  
  // Add days of the month
  for (let day = 1; day <= end.getDate(); day++) {
    const date = new Date(start.getFullYear(), start.getMonth(), day);
    currentWeek.push(date);
    
    if (currentWeek.length === 7) {
      weeks.push(currentWeek);
      currentWeek = [];
    }
  }
  
  // Fill the last week with empty days if needed
  if (currentWeek.length > 0) {
    while (currentWeek.length < 7) {
      currentWeek.push(null);
    }
    weeks.push(currentWeek);
  }
  
  return weeks;
};

const isDisabled = (date) => {
  if (!date) return true;
  if (props.minDate && date < props.minDate) return true;
  if (props.maxDate && date > props.maxDate) return true;
  return false;
};

const previousMonth = () => {
  currentMonth.value = subMonths(currentMonth.value, 1);
};

const nextMonth = () => {
  currentMonth.value = addMonths(currentMonth.value, 1);
};

const isSelected = (date) => {
  if (!date || !props.modelValue) return false;
  return isSameDay(date, props.modelValue);
};

const isToday = (date) => {
  if (!date) return false;
  return isDateToday(date);
};

const selectDate = (date) => {
  if (!date || isDisabled(date)) return;
  emit('update:modelValue', date);
};
</script>

<template>
  <div class="w-[var(--calendar-width)] p-3">
    <!-- Calendar Header -->
    <div class="flex justify-between items-center mb-4">
      <Button
        variant="outline"
        class="h-7 w-7 p-0"
        @click="previousMonth"
      >
        <ChevronLeft class="h-4 w-4" />
      </Button>
      <div class="font-medium">
        {{ format(currentMonth, 'MMMM yyyy') }}
      </div>
      <Button
        variant="outline"
        class="h-7 w-7 p-0"
        @click="nextMonth"
      >
        <ChevronRight class="h-4 w-4" />
      </Button>
    </div>

    <!-- Calendar Grid -->
    <div class="grid grid-cols-7 text-center text-sm mb-2">
      <div v-for="day in days" :key="day" class="text-muted-foreground">
        {{ day }}
      </div>
    </div>
    <div class="grid grid-cols-7 text-center">
      <template v-for="(week, weekIndex) in getWeeksInMonth(currentMonth)" :key="weekIndex">
        <button
          v-for="(date, dateIndex) in week"
          :key="`${weekIndex}-${dateIndex}`"
          :disabled="isDisabled(date)"
          :class="[
            'h-9 w-9 p-0 font-normal aria-selected:opacity-100',
            date && isToday(date) && 'text-accent-foreground',
            date && isSelected(date) && 'bg-primary text-primary-foreground hover:bg-primary hover:text-primary-foreground focus:bg-primary focus:text-primary-foreground',
            !date && 'text-muted-foreground opacity-50',
            date && !isSelected(date) && !isDisabled(date) && 'hover:bg-accent hover:text-accent-foreground',
            date && isDisabled(date) && 'text-muted-foreground opacity-50',
            'rounded-md',
          ]"
          @click="selectDate(date)"
        >
          <time
            v-if="date"
            :datetime="format(date, 'yyyy-MM-dd')"
          >
            {{ format(date, 'd') }}
          </time>
        </button>
      </template>
    </div>
  </div>
</template>

<style>
:root {
  --calendar-width: 300px;
}
</style>
