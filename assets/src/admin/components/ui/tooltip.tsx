import { Tooltip as TooltipPrimitive } from "@base-ui/react/tooltip"

import { cn } from "@/admin/lib/utils"

function TooltipProvider({
  delay = 0,
  ...props
}: TooltipPrimitive.Provider.Props) {
  return (
    <TooltipPrimitive.Provider
      data-slot="tooltip-provider"
      delay={delay}
      {...props}
    />
  )
}

function Tooltip({ ...props }: TooltipPrimitive.Root.Props) {
  return <TooltipPrimitive.Root data-slot="tooltip" {...props} />
}

function TooltipTrigger({ ...props }: TooltipPrimitive.Trigger.Props) {
  return <TooltipPrimitive.Trigger data-slot="tooltip-trigger" {...props} />
}

function TooltipContent({
  className,
  side = "top",
  sideOffset = 4,
  align = "center",
  alignOffset = 0,
  children,
  ...props
}: TooltipPrimitive.Popup.Props &
  Pick<
    TooltipPrimitive.Positioner.Props,
    "align" | "alignOffset" | "side" | "sideOffset"
  >) {
  return (
    <TooltipPrimitive.Portal>
      <TooltipPrimitive.Positioner
        align={align}
        alignOffset={alignOffset}
        side={side}
        sideOffset={sideOffset}
        className="sr-isolate sr-z-50"
      >
        <TooltipPrimitive.Popup
          data-slot="tooltip-content"
          className={cn(
            "sr-z-50 sr-inline-flex sr-w-fit sr-max-w-xs sr-origin-(--transform-origin) sr-items-center sr-gap-1.5 sr-rounded-md sr-bg-foreground sr-px-3 sr-py-1.5 sr-text-xs sr-text-background has-data-[slot=kbd]:sr-pr-1.5 data-[side=bottom]:sr-slide-in-from-top-2 data-[side=inline-end]:sr-slide-in-from-left-2 data-[side=inline-start]:sr-slide-in-from-right-2 data-[side=left]:sr-slide-in-from-right-2 data-[side=right]:sr-slide-in-from-left-2 data-[side=top]:sr-slide-in-from-bottom-2 **:data-[slot=kbd]:sr-relative **:data-[slot=kbd]:sr-isolate **:data-[slot=kbd]:sr-z-50 **:data-[slot=kbd]:sr-rounded-sm data-[state=delayed-open]:sr-animate-in data-[state=delayed-open]:sr-fade-in-0 data-[state=delayed-open]:sr-zoom-in-95 data-open:sr-animate-in data-open:sr-fade-in-0 data-open:sr-zoom-in-95 data-closed:sr-animate-out data-closed:sr-fade-out-0 data-closed:sr-zoom-out-95",
            className
          )}
          {...props}
        >
          {children}
          <TooltipPrimitive.Arrow className="sr-z-50 sr-size-2.5 sr-translate-y-[calc(-50%-2px)] sr-rotate-45 sr-rounded-[2px] sr-bg-foreground sr-fill-foreground data-[side=bottom]:sr-top-1 data-[side=inline-end]:sr-top-1/2! data-[side=inline-end]:-sr-left-1 data-[side=inline-end]:-sr-translate-y-1/2 data-[side=inline-start]:sr-top-1/2! data-[side=inline-start]:-sr-right-1 data-[side=inline-start]:-sr-translate-y-1/2 data-[side=left]:sr-top-1/2! data-[side=left]:-sr-right-1 data-[side=left]:-sr-translate-y-1/2 data-[side=right]:sr-top-1/2! data-[side=right]:-sr-left-1 data-[side=right]:-sr-translate-y-1/2 data-[side=top]:-sr-bottom-2.5" />
        </TooltipPrimitive.Popup>
      </TooltipPrimitive.Positioner>
    </TooltipPrimitive.Portal>
  )
}

export { Tooltip, TooltipTrigger, TooltipContent, TooltipProvider }
