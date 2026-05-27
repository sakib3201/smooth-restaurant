"use client"

import { Toggle as TogglePrimitive } from "@base-ui/react/toggle"
import { cva, type VariantProps } from "class-variance-authority"

import { cn } from "@/admin/lib/utils"

const toggleVariants = cva(
  "group/toggle sr-inline-flex sr-items-center sr-justify-center sr-gap-1 sr-rounded-lg sr-text-sm sr-font-medium sr-whitespace-nowrap sr-transition-all sr-outline-none hover:sr-bg-muted hover:sr-text-foreground focus-visible:sr-border-ring focus-visible:sr-ring-[3px] focus-visible:sr-ring-ring/50 disabled:sr-pointer-events-none disabled:sr-opacity-50 aria-invalid:sr-border-destructive aria-invalid:sr-ring-destructive/20 aria-pressed:sr-bg-muted data-[state=on]:sr-bg-muted dark:aria-invalid:sr-ring-destructive/40 [&_svg]:sr-pointer-events-none [&_svg]:sr-shrink-0 [&_svg:not([class*='size-'])]:sr-size-4",
  {
    variants: {
      variant: {
        default: "sr-bg-transparent",
        outline: "sr-border sr-border-input sr-bg-transparent hover:sr-bg-muted",
      },
      size: {
        default:
          "sr-h-8 sr-min-w-8 sr-px-2.5 has-data-[icon=inline-end]:sr-pr-2 has-data-[icon=inline-start]:sr-pl-2",
        sm: "sr-h-7 sr-min-w-7 sr-rounded-[min(var(--radius-md),12px)] sr-px-2.5 sr-text-[0.8rem] has-data-[icon=inline-end]:sr-pr-1.5 has-data-[icon=inline-start]:sr-pl-1.5 [&_svg:not([class*='size-'])]:sr-size-3.5",
        lg: "sr-h-9 sr-min-w-9 sr-px-2.5 has-data-[icon=inline-end]:sr-pr-2 has-data-[icon=inline-start]:sr-pl-2",
      },
    },
    defaultVariants: {
      variant: "default",
      size: "default",
    },
  }
)

function Toggle({
  className,
  variant = "default",
  size = "default",
  ...props
}: TogglePrimitive.Props & VariantProps<typeof toggleVariants>) {
  return (
    <TogglePrimitive
      data-slot="toggle"
      className={cn(toggleVariants({ variant, size, className }))}
      {...props}
    />
  )
}

export { Toggle, toggleVariants }
