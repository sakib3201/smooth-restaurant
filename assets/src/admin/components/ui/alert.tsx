import * as React from "react"
import { cva, type VariantProps } from "class-variance-authority"

import { cn } from "@/admin/lib/utils"

const alertVariants = cva(
  "group/alert sr-relative sr-grid sr-w-full sr-gap-0.5 sr-rounded-lg sr-border sr-px-2.5 sr-py-2 sr-text-left sr-text-sm has-data-[slot=alert-action]:sr-relative has-data-[slot=alert-action]:sr-pr-18 has-[>svg]:sr-grid-cols-[auto_1fr] has-[>svg]:sr-gap-x-2 *:[svg]:sr-row-span-2 *:[svg]:sr-translate-y-0.5 *:[svg]:sr-text-current *:[svg:not([class*='size-'])]:sr-size-4",
  {
    variants: {
      variant: {
        default: "sr-bg-card sr-text-card-foreground",
        destructive:
          "sr-bg-card sr-text-destructive *:data-[slot=alert-description]:sr-text-destructive/90 *:[svg]:sr-text-current",
      },
    },
    defaultVariants: {
      variant: "default",
    },
  }
)

function Alert({
  className,
  variant,
  ...props
}: React.ComponentProps<"div"> & VariantProps<typeof alertVariants>) {
  return (
    <div
      data-slot="alert"
      role="alert"
      className={cn(alertVariants({ variant }), className)}
      {...props}
    />
  )
}

function AlertTitle({ className, ...props }: React.ComponentProps<"div">) {
  return (
    <div
      data-slot="alert-title"
      className={cn(
        "sr-font-medium group-has-[>svg]/alert:sr-col-start-2 [&_a]:sr-underline [&_a]:sr-underline-offset-3 [&_a]:hover:sr-text-foreground",
        className
      )}
      {...props}
    />
  )
}

function AlertDescription({
  className,
  ...props
}: React.ComponentProps<"div">) {
  return (
    <div
      data-slot="alert-description"
      className={cn(
        "sr-text-sm sr-text-balance sr-text-muted-foreground md:sr-text-pretty [&_a]:sr-underline [&_a]:sr-underline-offset-3 [&_a]:hover:sr-text-foreground [&_p:not(:last-child)]:sr-mb-4",
        className
      )}
      {...props}
    />
  )
}

function AlertAction({ className, ...props }: React.ComponentProps<"div">) {
  return (
    <div
      data-slot="alert-action"
      className={cn("sr-absolute sr-top-2 sr-right-2", className)}
      {...props}
    />
  )
}

export { Alert, AlertTitle, AlertDescription, AlertAction }
