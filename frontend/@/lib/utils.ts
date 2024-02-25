import { type ClassValue, clsx } from "clsx";
import { FuelType, TransmissionType } from "../../src/api-client";
import { twMerge } from "tailwind-merge";

export function cn(...inputs: ClassValue[]) {
  return twMerge(clsx(inputs));
}

export function getFuelTypeDisplayName(x: FuelType | undefined | null) {
  if (!x) {
    return "Любой тип топлива";
  }

  const dict = {
    [FuelType.Gas]: "Газ",
    [FuelType.Gasoline]: "Бензин",
  };

  return dict[x];
}

export function getTransmissionDisplayName(
  x: TransmissionType | undefined | null
) {
  if (!x) {
    return "Любой тип трансмиссии";
  }

  const dict = {
    [TransmissionType.Automatic]: "Автоматическая",
    [TransmissionType.Mechanics]: "Ручная",
  };

  return dict[x];
}

export const formatRoubles = (amount: number) =>
  new Intl.NumberFormat("ru-RU", {
    style: "currency",
    maximumSignificantDigits: 3,
    currency: "RUB",
  }).format(amount);
