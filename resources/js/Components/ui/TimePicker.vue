<script setup>
import { ref, computed } from 'vue';
import { Button } from "@/Components/ui/button";
import { Input } from "@/Components/ui/input";
import { cn } from "@/lib/utils";

const props = defineProps({
  modelValue: {
    type: String,
    required: true
  }
});

const emit = defineEmits(['update:modelValue']);

// Parse time string into components
const parseTime = (timeString) => {
  if (!timeString) return { hours: '12', minutes: '00', period: 'AM' };
  
  const time = new Date(`2000-01-01 ${timeString}`);
  let hours = time.getHours();
  const minutes = time.getMinutes();
  const period = hours >= 12 ? 'PM' : 'AM';
  
  // Convert to 12-hour format
  if (hours > 12) hours -= 12;
  if (hours === 0) hours = 12;
  
  return {
    hours: hours.toString().padStart(2, '0'),
    minutes: minutes.toString().padStart(2, '0'),
    period
  };
};

const time = parseTime(props.modelValue);
const hours = ref(time.hours);
const minutes = ref(time.minutes);
const period = ref(time.period);

// Format time for v-model
const updateModelValue = () => {
  let h = parseInt(hours.value);
  const m = minutes.value;
  
  // Convert to 24-hour format
  if (period.value === 'PM' && h !== 12) h += 12;
  if (period.value === 'AM' && h === 12) h = 0;
  
  emit('update:modelValue', `${h.toString().padStart(2, '0')}:${m.padStart(2, '0')}`);
};

// Validate input values
const validateHours = (value) => {
  let num = parseInt(value) || 0;
  if (num < 1) num = 1;
  if (num > 12) num = 12;
  hours.value = num.toString().padStart(2, '0');
  updateModelValue();
};

const validateMinutes = (value) => {
  let num = parseInt(value) || 0;
  if (num < 0) num = 0;
  if (num > 59) num = 59;
  minutes.value = num.toString().padStart(2, '0');
  updateModelValue();
};

const togglePeriod = () => {
  period.value = period.value === 'AM' ? 'PM' : 'AM';
  updateModelValue();
};
</script>

<template>
  <div class="flex items-center gap-2">
    <Input
      v-model="hours"
      type="text"
      maxlength="2"
      class="w-14 text-center"
      @blur="validateHours(hours)"
    />
    <span class="text-lg text-muted-foreground">:</span>
    <Input
      v-model="minutes"
      type="text"
      maxlength="2"
      class="w-14 text-center"
      @blur="validateMinutes(minutes)"
    />
    <Button
      type="button"
      variant="outline"
      class="w-14"
      @click="togglePeriod"
    >
      {{ period }}
    </Button>
  </div>
</template>
