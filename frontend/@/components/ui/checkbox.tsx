import * as React from "react";
import * as CheckboxPrimitive from "@radix-ui/react-checkbox";
import { Check } from "lucide-react";

import { cn } from "@/lib/utils";
import { useState } from "react";

const CheckboxShad = React.forwardRef<
  React.ElementRef<typeof CheckboxPrimitive.Root>,
  React.ComponentPropsWithoutRef<typeof CheckboxPrimitive.Root>
>(({ className, ...props }, ref) => (
  <CheckboxPrimitive.Root
    ref={ref}
    className={cn(
      `peer  shrink-0 border
       border-pale ring-offset-white focus-visible:outline-none
        focus-visible:ring-2 focus-visible:ring-slate-950 
        focus-visible:ring-offset-2 disabled:cursor-not-allowed 
        disabled:opacity-50 data-[state=checked]:bg-yellow
         data-[state=checked]:text-slate-50 rounded-full w-7 h-7 bg-pale`,
      className
    )}
    {...props}
  >
    <CheckboxPrimitive.Indicator
      className={cn("flex items-center justify-center text-current")}
    >
      <Check className="w-4 h-4" />
    </CheckboxPrimitive.Indicator>
  </CheckboxPrimitive.Root>
));
CheckboxShad.displayName = CheckboxPrimitive.Root.displayName;

const Checkbox = (props: {
  title: string;
  isChecked: boolean;
  onCheckedChange: (isChecked: boolean) => void;
}) => {
  const handleTitleClick = () => {
    props.onCheckedChange(!props.isChecked);
  };
  return (
    <span className="flex items-center justify-between mb-4 space-x-2">
      <p className="text-xl md:text-sm" onClick={handleTitleClick}>
        {props.title}
      </p>
      <CheckboxShad
        onCheckedChange={props.onCheckedChange}
        checked={props.isChecked}
      />
    </span>
  );
};

export { Checkbox };
