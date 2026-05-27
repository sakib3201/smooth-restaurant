"use client"

import { Checkbox as CheckboxPrimitive } from "@base-ui/react/checkbox"

import { cn } from "@/admin/lib/utils"
import { CheckIcon } from "lucide-react"

function Checkbox({ className, ...props }: CheckboxPrimitive.Root.Props) {
  return (
    <CheckboxPrimitive.Root
      data-slot="checkbox"
      className={cn(
        "sr-peer sr-relative sr-flex sr-size-4 sr-shrink-0 sr-items-center sr-justify-center sr-rounded-[4px] sr-border sr-border-input sr-transition-colors sr-outline-none group-has-disabled/field:sr-opacity-50 after:sr-absolute after:-sr-inset-x-3 after:-sr-inset-y-2 focus-visible:sr-border-ring focus-visible:sr-ring-3 focus-visible:sr-ring-ring/50 disabled:sr-cursor-not-allowed disabled:sr-opacity-50 aria-invalid:sr-border-destructive aria-invalid:sr-ring-3 aria-invalid:sr-ring-destructive/20 aria-invalid:aria-checked:sr-border-primary dark:sr-bg-input/30 dark:aria-invalid:sr-border-destructive/50 dark:aria-invalid:sr-ring-destructive/40 data-checked:sr-border-primary data-checked:sr-bg-primary data-checked:sr-text-primary-foreground dark:data-checked:sr-bg-primary",
        className
      )}
      {...props}
    >
      <CheckboxPrimitive.Indicator
        data-slot="checkbox-indicator"
        className="sr-grid sr-place-content-center sr-text-current sr-transition-none [&>svg]:sr-size-3.5"
      >
        <CheckIcon
        />
      </CheckboxPrimitive.Indicator>
    </CheckboxPrimitive.Root>
  )
}

export { Checkbox }
