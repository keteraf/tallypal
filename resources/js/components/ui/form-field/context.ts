import { computed, inject, provide, type ComputedRef, type InjectionKey } from 'vue'

export interface FormFieldContext {
  invalid: ComputedRef<boolean>
  describedBy: ComputedRef<string | undefined>
}

const formFieldKey: InjectionKey<FormFieldContext> = Symbol('form-field')

export function provideFormField(context: FormFieldContext): void {
  provide(formFieldKey, context)
}

/**
 * Error wiring a control gets from its surrounding `FormField`: `aria-invalid`
 * and `aria-describedby` (→ the message id). Both are empty outside a field.
 */
export function useFormFieldAria(): {
  ariaInvalid: ComputedRef<true | undefined>
  ariaDescribedby: ComputedRef<string | undefined>
} {
  const context = inject(formFieldKey, null)

  return {
    ariaInvalid: computed(() => (context?.invalid.value ? true : undefined)),
    ariaDescribedby: computed(() => (context?.invalid.value ? context.describedBy.value : undefined)),
  }
}
