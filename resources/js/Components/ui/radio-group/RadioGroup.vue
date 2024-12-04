<!-- resources/js/components/ui/radio-group/RadioGroup.vue -->
<script setup>
import { RadioGroupRoot } from 'radix-vue'  // Changed from RadioGroup to RadioGroupRoot
import { useForwardPropsEmits } from 'radix-vue'
import { computed } from 'vue'

const props = defineProps({
  modelValue: { type: [String, Number], required: false },
  defaultValue: { type: [String, Number], required: false },
  disabled: { type: Boolean, required: false },
  name: { type: String, required: false },
  required: { type: Boolean, required: false },
  class: { type: null, required: false },
})

const emits = defineEmits(['update:modelValue'])

const delegatedProps = computed(() => {
  const { class: _, ...delegated } = props
  return delegated
})

const forwarded = useForwardPropsEmits(delegatedProps, emits)
</script>

<template>
  <RadioGroupRoot v-bind="forwarded">
    <slot />
  </RadioGroupRoot>
</template>
