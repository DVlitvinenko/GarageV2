import { type ClassValue, clsx } from "clsx"
import { FuelType } from "src/api-client"
import { twMerge } from "tailwind-merge"

export function cn(...inputs: ClassValue[]) {
  return twMerge(clsx(inputs))
}