import { cva, type VariantProps } from "class-variance-authority"

import { cn } from "@/admin/lib/utils"

function Empty({ className, ...props }: React.ComponentProps<"div">) {
  return (
    <div
      data-slot="empty"
      className={cn(
        "sr-flex sr-w-full sr-min-w-0 sr-flex-1 sr-flex-col sr-items-center sr-justify-center sr-gap-4 sr-rounded-xl sr-border-dashed sr-p-6 sr-text-center sr-text-balance",
        className
      )}
      {...props}
    />
  )
}

function EmptyHeader({ className, ...props }: React.ComponentProps<"div">) {
  return (
    <div
      data-slot="empty-header"
      className={cn("sr-flex sr-max-w-sm sr-flex-col sr-items-center sr-gap-2", className)}
      {...props}
    />
  )
}

const emptyMediaVariants = cva(
  "sr-mb-2 sr-flex sr-shrink-0 sr-items-center sr-justify-center [&_svg]:sr-pointer-events-none [&_svg]:sr-shrink-0",
  {
    variants: {
      variant: {
        default: "sr-bg-transparent",
        icon: "sr-flex sr-size-8 sr-shrink-0 sr-items-center sr-justify-center sr-rounded-lg sr-bg-muted sr-text-foreground [&_svg:not([class*='size-'])]:sr-size-4",
      },
    },
    defaultVariants: {
      variant: "default",
    },
  }
)

function EmptyMedia({
  className,
  variant = "default",
  ...props
}: React.ComponentProps<"div"> & VariantProps<typeof emptyMediaVariants>) {
  return (
    <div
      data-slot="empty-icon"
      data-variant={variant}
      className={cn(emptyMediaVariants({ variant, className }))}
      {...props}
    />
  )
}

function EmptyTitle({ className, ...props }: React.ComponentProps<"div">) {
  return (
    <div
      data-slot="empty-title"
      className={cn(
        "sr-font-heading sr-text-sm sr-font-medium sr-tracking-tight",
        className
      )}
      {...props}
    />
  )
}

function EmptyDescription({ className, ...props }: React.ComponentProps<"p">) {
  return (
    <div
      data-slot="empty-description"
      className={cn(
        "sr-text-sm/relaxed sr-text-muted-foreground [&>a]:sr-underline [&>a]:sr-underline-offset-4 [&>a:hover]:sr-text-primary",
        className
      )}
      {...props}
    />
  )
}

function EmptyContent({ className, ...props }: React.ComponentProps<"div">) {
  return (
    <div
      data-slot="empty-content"
      className={cn(
        "sr-flex sr-w-full sr-max-w-sm sr-min-w-0 sr-flex-col sr-items-center sr-gap-2.5 sr-text-sm sr-text-balance",
        className
      )}
      {...props}
    />
  )
}

export {
  Empty,
  EmptyHeader,
  EmptyTitle,
  EmptyDescription,
  EmptyContent,
  EmptyMedia,
}
