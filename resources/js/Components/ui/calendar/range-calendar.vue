<script setup>
import { ref, computed } from 'vue';
import { ChevronLeft, ChevronRight } from 'lucide-vue-next';
import { Button } from '@/Components/ui/button';
import { cn } from '@/lib/utils';
import { 
  addDays, 
  format, 
  addMonths, 
  subMonths, 
  isSameMonth, 
  isSameDay, 
  isWithinInterval,
  isToday as isDateToday,
  isBefore,
  isAfter,
} from 'date-fns';

const props = defineProps({
  modelValue: {
    type: Object,
    required: true,
  },
  minValue: {
    type: Date,
    required: false,
  },
  maxValue: {
    type: Date,
    required: false,
  },
  numberOfMonths: {
    type: Number,
    default: 1,
  },
  initialFocus: {
    type: Boolean,
    default: false,
  },
  isDateUnavailable: {
    type: Function,
    default: () => false,
  },
});

const emit = defineEmits(['update:modelValue']);

const currentMonth = ref(props.modelValue?.start || new Date());
const months = computed(() => {
  return Array.from({ length: props.numberOfMonths }, (_, i) => 
    addMonths(currentMonth.value, i)
  );
});

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
  if (props.minValue && isBefore(date, props.minValue)) return true;
  if (props.maxValue && isAfter(date, props.maxValue)) return true;
  return props.isDateUnavailable(date);
};

const previousMonth = () => {
  currentMonth.value = subMonths(currentMonth.value, 1);
};

const nextMonth = () => {
  currentMonth.value = addMonths(currentMonth.value, 1);
};

const isSelected = (date) => {
  if (!date || !props.modelValue.start || !props.modelValue.end) return false;
  return isWithinInterval(date, {
    start: props.modelValue.start,
    end: props.modelValue.end,
  });
};

const isRangeStart = (date) => {
  if (!date || !props.modelValue.start) return false;
  return isSameDay(date, props.modelValue.start);
};

const isRangeEnd = (date) => {
  if (!date || !props.modelValue.end) return false;
  return isSameDay(date, props.modelValue.end);
};

const isToday = (date) => {
  if (!date) return false;
  return isDateToday(date);
};

const selectDate = (date) => {
  if (!date || isDisabled(date)) return;

  if (!props.modelValue.start || (props.modelValue.start && props.modelValue.end)) {
    emit('update:modelValue', { start: date, end: null });
  } else {
    if (isBefore(date, props.modelValue.start)) {
      emit('update:modelValue', { start: date, end: props.modelValue.start });
    } else {
      emit('update:modelValue', { start: props.modelValue.start, end: date });
    }
  }
};
</script>

<template>
  <div class="flex gap-4">
    <div 
      v-for="(month, monthIndex) in months"
      :key="monthIndex"
      class="w-[var(--calendar-width)] p-3"
    >
      <!-- Calendar Header -->
      <div class="flex justify-between items-center mb-4">
        <Button
          v-if="monthIndex === 0"
          variant="outline"
          class="h-7 w-7 p-0"
          @click="previousMonth"
        >
          <ChevronLeft class="h-4 w-4" />
        </Button>
        <div v-else class="w-7"></div>
        <div class="font-medium">
          {{ format(month, 'MMMM yyyy') }}
        </div>
        <Button
          v-if="monthIndex === months.length - 1"
          variant="outline"
          class="h-7 w-7 p-0"
          @click="nextMonth"
        >
          <ChevronRight class="h-4 w-4" />
        </Button>
        <div v-else class="w-7"></div>
      </div>

      <!-- Calendar Grid -->
      <div class="grid grid-cols-7 text-center text-sm mb-2">
        <div v-for="day in days" :key="day" class="text-muted-foreground">
          {{ day }}
        </div>
      </div>
      <div class="grid grid-cols-7 text-center">
        <template v-for="(week, weekIndex) in getWeeksInMonth(month)" :key="weekIndex">
          <button
            v-for="(date, dateIndex) in week"
            :key="`${weekIndex}-${dateIndex}`"
            :disabled="isDisabled(date)"
            :class="[
              'h-9 w-9 p-0 font-normal aria-selected:opacity-100',
              date && isToday(date) && 'text-accent-foreground',
              date && isSelected(date) && 'bg-primary/50',
              date && isRangeStart(date) && 'bg-primary text-primary-foreground hover:bg-primary hover:text-primary-foreground focus:bg-primary focus:text-primary-foreground rounded-l-md',
              date && isRangeEnd(date) && 'bg-primary text-primary-foreground hover:bg-primary hover:text-primary-foreground focus:bg-primary focus:text-primary-foreground rounded-r-md',
              !date && 'text-muted-foreground opacity-50',
              date && !isSelected(date) && !isDisabled(date) && 'hover:bg-accent hover:text-accent-foreground',
              date && isDisabled(date) && 'text-muted-foreground opacity-50',
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
  </div>
</template>

<style>
:root {
  --calendar-width: 300px;
}
</style>
