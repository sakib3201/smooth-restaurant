import { Switch as SwitchPrimitive } from "@base-ui/react/switch"

import { cn } from "@/admin/lib/utils"

function Switch({
  className,
  size = "default",
  ...props
}: SwitchPrimitive.Root.Props & {
  size?: "sm" | "default"
}) {
  return (
    <SwitchPrimitive.Root
      data-slot="switch"
      data-size={size}
      className={cn(
        "sr-peer group/switch sr-relative sr-inline-flex sr-shrink-0 sr-items-center sr-rounded-full sr-border sr-border-transparent sr-transition-all sr-outline-none after:sr-absolute after:-sr-inset-x-3 after:-sr-inset-y-2 focus-visible:sr-border-ring focus-visible:sr-ring-3 focus-visible:sr-ring-ring/50 aria-invalid:sr-border-destructive aria-invalid:sr-ring-3 aria-invalid:sr-ring-destructive/20 data-[size=default]:sr-h-[18.4px] data-[size=default]:sr-w-[32px] data-[size=sm]:sr-h-[14px] data-[size=sm]:sr-w-[24px] dark:aria-invalid:sr-border-destructive/50 dark:aria-invalid:sr-ring-destructive/40 data-checked:sr-bg-primary data-unchecked:sr-bg-input dark:data-unchecked:sr-bg-input/80 data-disabled:sr-cursor-not-allowed data-disabled:sr-opacity-50",
        className
      )}
      {...props}
    >
      <SwitchPrimitive.Thumb
        data-slot="switch-thumb"
        className="sr-pointer-events-none sr-block sr-rounded-full sr-bg-background sr-ring-0 sr-transition-transform group-data-[size=default]/switch:sr-size-4 group-data-[size=sm]/switch:sr-size-3 group-data-[size=default]/switch:data-checked:sr-translate-x-[calc(100%-2px)] group-data-[size=sm]/switch:data-checked:sr-translate-x-[calc(100%-2px)] dark:data-checked:sr-bg-primary-foreground group-data-[size=default]/switch:data-unchecked:sr-translate-x-0 group-data-[size=sm]/switch:data-unchecked:sr-translate-x-0 dark:data-unchecked:sr-bg-foreground"
      />
    </SwitchPrimitive.Root>
  )
}

export { Switch }
