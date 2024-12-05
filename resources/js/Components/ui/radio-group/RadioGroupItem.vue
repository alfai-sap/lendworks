<!-- resources/js/components/ui/radio-group/RadioGroupItem.vue -->
<script setup>
import { RadioGroupIndicator, RadioGroupItem as RadioGroupItemRoot } from 'radix-vue'
import { cn } from '@/lib/utils'
import { computed } from 'vue'

const props = defineProps({
  value: { type: [String, Number], required: true },
  disabled: { type: Boolean, required: false },
  class: { type: null, required: false },
})

const delegatedProps = computed(() => {
  const { class: _, ...delegated } = props
  return delegated
})
</script>

<template>
  <RadioGroupItemRoot
    v-bind="delegatedProps"
    :class="cn('aspect-square h-4 w-4 rounded-full border border-primary text-primary ring-offset-background focus:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50', props.class)"
  >
    <RadioGroupIndicator class="flex items-center justify-center">
      <div class="h-2.5 w-2.5 rounded-full bg-primary" />
    </RadioGroupIndicator>
  </RadioGroupItemRoot>
</template>