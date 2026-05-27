import * as React from "react"
import { Select as SelectPrimitive } from "@base-ui/react/select"

import { cn } from "@/admin/lib/utils"
import { ChevronDownIcon, CheckIcon, ChevronUpIcon } from "lucide-react"

const Select = SelectPrimitive.Root

function SelectGroup({ className, ...props }: SelectPrimitive.Group.Props) {
  return (
    <SelectPrimitive.Group
      data-slot="select-group"
      className={cn("sr-scroll-my-1 sr-p-1", className)}
      {...props}
    />
  )
}

function SelectValue({ className, ...props }: SelectPrimitive.Value.Props) {
  return (
    <SelectPrimitive.Value
      data-slot="select-value"
      className={cn("sr-flex sr-flex-1 sr-text-left", className)}
      {...props}
    />
  )
}

function SelectTrigger({
  className,
  size = "default",
  children,
  ...props
}: SelectPrimitive.Trigger.Props & {
  size?: "sm" | "default"
}) {
  return (
    <SelectPrimitive.Trigger
      data-slot="select-trigger"
      data-size={size}
      className={cn(
        "sr-flex sr-w-fit sr-items-center sr-justify-between sr-gap-1.5 sr-rounded-lg sr-border sr-border-input sr-bg-transparent sr-py-2 sr-pr-2 sr-pl-2.5 sr-text-sm sr-whitespace-nowrap sr-transition-colors sr-outline-none sr-select-none focus-visible:sr-border-ring focus-visible:sr-ring-3 focus-visible:sr-ring-ring/50 disabled:sr-cursor-not-allowed disabled:sr-opacity-50 aria-invalid:sr-border-destructive aria-invalid:sr-ring-3 aria-invalid:sr-ring-destructive/20 data-placeholder:sr-text-muted-foreground data-[size=default]:sr-h-8 data-[size=sm]:sr-h-7 data-[size=sm]:sr-rounded-[min(var(--radius-md),10px)] *:data-[slot=select-value]:sr-line-clamp-1 *:data-[slot=select-value]:sr-flex *:data-[slot=select-value]:sr-items-center *:data-[slot=select-value]:sr-gap-1.5 dark:sr-bg-input/30 dark:hover:sr-bg-input/50 dark:aria-invalid:sr-border-destructive/50 dark:aria-invalid:sr-ring-destructive/40 [&_svg]:sr-pointer-events-none [&_svg]:sr-shrink-0 [&_svg:not([class*='size-'])]:sr-size-4",
        className
      )}
      {...props}
    >
      {children}
      <SelectPrimitive.Icon
        render={
          <ChevronDownIcon className="sr-pointer-events-none sr-size-4 sr-text-muted-foreground" />
        }
      />
    </SelectPrimitive.Trigger>
  )
}

function SelectContent({
  className,
  children,
  side = "bottom",
  sideOffset = 4,
  align = "center",
  alignOffset = 0,
  alignItemWithTrigger = true,
  ...props
}: SelectPrimitive.Popup.Props &
  Pick<
    SelectPrimitive.Positioner.Props,
    "align" | "alignOffset" | "side" | "sideOffset" | "alignItemWithTrigger"
  >) {
  return (
    <SelectPrimitive.Portal>
      <SelectPrimitive.Positioner
        side={side}
        sideOffset={sideOffset}
        align={align}
        alignOffset={alignOffset}
        alignItemWithTrigger={alignItemWithTrigger}
        className="sr-isolate sr-z-50"
      >
        <SelectPrimitive.Popup
          data-slot="select-content"
          data-align-trigger={alignItemWithTrigger}
          className={cn("sr-relative sr-isolate sr-z-50 sr-max-h-(--available-height) sr-w-(--anchor-width) sr-min-w-36 sr-origin-(--transform-origin) sr-overflow-x-hidden sr-overflow-y-auto sr-rounded-lg sr-bg-popover sr-text-popover-foreground sr-shadow-md sr-ring-1 sr-ring-foreground/10 sr-duration-100 data-[align-trigger=true]:sr-animate-none data-[side=bottom]:sr-slide-in-from-top-2 data-[side=inline-end]:sr-slide-in-from-left-2 data-[side=inline-start]:sr-slide-in-from-right-2 data-[side=left]:sr-slide-in-from-right-2 data-[side=right]:sr-slide-in-from-left-2 data-[side=top]:sr-slide-in-from-bottom-2 data-open:sr-animate-in data-open:sr-fade-in-0 data-open:sr-zoom-in-95 data-closed:sr-animate-out data-closed:sr-fade-out-0 data-closed:sr-zoom-out-95", className )}
          {...props}
        >
          <SelectScrollUpButton />
          <SelectPrimitive.List>{children}</SelectPrimitive.List>
          <SelectScrollDownButton />
        </SelectPrimitive.Popup>
      </SelectPrimitive.Positioner>
    </SelectPrimitive.Portal>
  )
}

function SelectLabel({
  className,
  ...props
}: SelectPrimitive.GroupLabel.Props) {
  return (
    <SelectPrimitive.GroupLabel
      data-slot="select-label"
      className={cn("sr-px-1.5 sr-py-1 sr-text-xs sr-text-muted-foreground", className)}
      {...props}
    />
  )
}

function SelectItem({
  className,
  children,
  ...props
}: SelectPrimitive.Item.Props) {
  return (
    <SelectPrimitive.Item
      data-slot="select-item"
      className={cn(
        "sr-relative sr-flex sr-w-full sr-cursor-default sr-items-center sr-gap-1.5 sr-rounded-md sr-py-1 sr-pr-8 sr-pl-1.5 sr-text-sm sr-outline-hidden sr-select-none focus:sr-bg-accent focus:sr-text-accent-foreground not-data-[variant=destructive]:focus:**:sr-text-accent-foreground data-disabled:sr-pointer-events-none data-disabled:sr-opacity-50 [&_svg]:sr-pointer-events-none [&_svg]:sr-shrink-0 [&_svg:not([class*='size-'])]:sr-size-4 *:[span]:last:sr-flex *:[span]:last:sr-items-center *:[span]:last:sr-gap-2",
        className
      )}
      {...props}
    >
      <SelectPrimitive.ItemText className="sr-flex sr-flex-1 sr-shrink-0 sr-gap-2 sr-whitespace-nowrap">
        {children}
      </SelectPrimitive.ItemText>
      <SelectPrimitive.ItemIndicator
        render={
          <span className="sr-pointer-events-none sr-absolute sr-right-2 sr-flex sr-size-4 sr-items-center sr-justify-center" />
        }
      >
        <CheckIcon className="sr-pointer-events-none" />
      </SelectPrimitive.ItemIndicator>
    </SelectPrimitive.Item>
  )
}

function SelectSeparator({
  className,
  ...props
}: SelectPrimitive.Separator.Props) {
  return (
    <SelectPrimitive.Separator
      data-slot="select-separator"
      className={cn("sr-pointer-events-none -sr-mx-1 sr-my-1 sr-h-px sr-bg-border", className)}
      {...props}
    />
  )
}

function SelectScrollUpButton({
  className,
  ...props
}: React.ComponentProps<typeof SelectPrimitive.ScrollUpArrow>) {
  return (
    <SelectPrimitive.ScrollUpArrow
      data-slot="select-scroll-up-button"
      className={cn(
        "sr-top-0 sr-z-10 sr-flex sr-w-full sr-cursor-default sr-items-center sr-justify-center sr-bg-popover sr-py-1 [&_svg:not([class*='size-'])]:sr-size-4",
        className
      )}
      {...props}
    >
      <ChevronUpIcon
      />
    </SelectPrimitive.ScrollUpArrow>
  )
}

function SelectScrollDownButton({
  className,
  ...props
}: React.ComponentProps<typeof SelectPrimitive.ScrollDownArrow>) {
  return (
    <SelectPrimitive.ScrollDownArrow
      data-slot="select-scroll-down-button"
      className={cn(
        "sr-bottom-0 sr-z-10 sr-flex sr-w-full sr-cursor-default sr-items-center sr-justify-center sr-bg-popover sr-py-1 [&_svg:not([class*='size-'])]:sr-size-4",
        className
      )}
      {...props}
    >
      <ChevronDownIcon
      />
    </SelectPrimitive.ScrollDownArrow>
  )
}

export {
  Select,
  SelectContent,
  SelectGroup,
  SelectItem,
  SelectLabel,
  SelectScrollDownButton,
  SelectScrollUpButton,
  SelectSeparator,
  SelectTrigger,
  SelectValue,
}
