import { type ClassValue, clsx } from "clsx";
import { FuelType, TransmissionType } from "../../src/api-client";
import { twMerge } from "tailwind-merge";
import { format } from "date-fns";

export function cn(...inputs: ClassValue[]) {
  return twMerge(clsx(inputs));
}

export function getFuelTypeDisplayName(x: FuelType | undefined | null) {
  if (!x) {
    return "Топливо";
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
    return "Трансмиссия";
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

export function formatWorkingTime(
  startHour: number,
  startMinute: number,
  endHour: number,
  endMinute: number
) {
  return `${startHour}${startMinute}` !== `${endHour}${endMinute}`
    ? `${format(
        new Date(2000, 0, 0, startHour, startMinute),
        "HH:mm"
      )} - ${format(new Date(2000, 0, 0, endHour, endMinute), "HH:mm")}`
    : "Выходной";
}
