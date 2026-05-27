import * as React from "react"
import { Input as InputPrimitive } from "@base-ui/react/input"

import { cn } from "@/admin/lib/utils"

function Input({ className, type, ...props }: React.ComponentProps<"input">) {
  return (
    <InputPrimitive
      type={type}
      data-slot="input"
      className={cn(
        "sr-h-8 sr-w-full sr-min-w-0 sr-rounded-lg sr-border sr-border-input sr-bg-transparent sr-px-2.5 sr-py-1 sr-text-base sr-transition-colors sr-outline-none file:sr-inline-flex file:sr-h-6 file:sr-border-0 file:sr-bg-transparent file:sr-text-sm file:sr-font-medium file:sr-text-foreground placeholder:sr-text-muted-foreground focus-visible:sr-border-ring focus-visible:sr-ring-3 focus-visible:sr-ring-ring/50 disabled:sr-pointer-events-none disabled:sr-cursor-not-allowed disabled:sr-bg-input/50 disabled:sr-opacity-50 aria-invalid:sr-border-destructive aria-invalid:sr-ring-3 aria-invalid:sr-ring-destructive/20 md:sr-text-sm dark:sr-bg-input/30 dark:disabled:sr-bg-input/80 dark:aria-invalid:sr-border-destructive/50 dark:aria-invalid:sr-ring-destructive/40",
        className
      )}
      {...props}
    />
  )
}

export { Input }
