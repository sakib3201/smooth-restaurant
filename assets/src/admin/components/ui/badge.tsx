import { mergeProps } from "@base-ui/react/merge-props"
import { useRender } from "@base-ui/react/use-render"
import { cva, type VariantProps } from "class-variance-authority"

import { cn } from "@/admin/lib/utils"

const badgeVariants = cva(
  "group/badge sr-inline-flex sr-h-5 sr-w-fit sr-shrink-0 sr-items-center sr-justify-center sr-gap-1 sr-overflow-hidden sr-rounded-4xl sr-border sr-border-transparent sr-px-2 sr-py-0.5 sr-text-xs sr-font-medium sr-whitespace-nowrap sr-transition-all focus-visible:sr-border-ring focus-visible:sr-ring-[3px] focus-visible:sr-ring-ring/50 has-data-[icon=inline-end]:sr-pr-1.5 has-data-[icon=inline-start]:sr-pl-1.5 aria-invalid:sr-border-destructive aria-invalid:sr-ring-destructive/20 dark:aria-invalid:sr-ring-destructive/40 [&>svg]:sr-pointer-events-none [&>svg]:sr-size-3!",
  {
    variants: {
      variant: {
        default: "sr-bg-primary sr-text-primary-foreground [a]:hover:sr-bg-primary/80",
        secondary:
          "sr-bg-secondary sr-text-secondary-foreground [a]:hover:sr-bg-secondary/80",
        destructive:
          "sr-bg-destructive/10 sr-text-destructive focus-visible:sr-ring-destructive/20 dark:sr-bg-destructive/20 dark:focus-visible:sr-ring-destructive/40 [a]:hover:sr-bg-destructive/20",
        outline:
          "sr-border-border sr-text-foreground [a]:hover:sr-bg-muted [a]:hover:sr-text-muted-foreground",
        ghost:
          "hover:sr-bg-muted hover:sr-text-muted-foreground dark:hover:sr-bg-muted/50",
        link: "sr-text-primary sr-underline-offset-4 hover:sr-underline",
      },
    },
    defaultVariants: {
      variant: "default",
    },
  }
)

function Badge({
  className,
  variant = "default",
  render,
  ...props
}: useRender.ComponentProps<"span"> & VariantProps<typeof badgeVariants>) {
  return useRender({
    defaultTagName: "span",
    props: mergeProps<"span">(
      {
        className: cn(badgeVariants({ variant }), className),
      },
      props
    ),
    render,
    state: {
      slot: "badge",
      variant,
    },
  })
}

export { Badge, badgeVariants }
