"use client"

import { Separator as SeparatorPrimitive } from "@base-ui/react/separator"

import { cn } from "@/admin/lib/utils"

function Separator({
  className,
  orientation = "horizontal",
  ...props
}: SeparatorPrimitive.Props) {
  return (
    <SeparatorPrimitive
      data-slot="separator"
      orientation={orientation}
      className={cn(
        "sr-shrink-0 sr-bg-border data-horizontal:sr-h-px data-horizontal:sr-w-full data-vertical:sr-w-px data-vertical:sr-self-stretch",
        className
      )}
      {...props}
    />
  )
}

export { Separator }
