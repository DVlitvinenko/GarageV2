import * as React from "react";
import { cva, type VariantProps } from "class-variance-authority";

import { cn } from "@/lib/utils";

const badgeVariants = cva(
  "inline-flex items-center h-10 text-nowrap whitespace-nowrap rounded-xl px-2.5 py-0.5 text-base font-regular text-black transition-colors focus:outline-none",
  {
    variants: {
      variant: {
        default: "bg-grey",
        secondary: "bg-white",
        card: "bg-white border border-pale px-3 ",
        schema:
          "bg-white border border-pale flex-col items-start justify-start flex-grow h-full px-2 text-lg font-bold text-wrap",
      },
    },
    defaultVariants: {
      variant: "default",
    },
  }
);

export interface BadgeProps
  extends React.HTMLAttributes<HTMLDivElement>,
    VariantProps<typeof badgeVariants> {}

function Badge({ className, variant, ...props }: BadgeProps) {
  return (
    <div className={cn(badgeVariants({ variant }), className)} {...props} />
  );
}

export { Badge, badgeVariants };
