import { type ClassValue, clsx } from "clsx";
import { DayOfWeek, FuelType, TransmissionType } from "../../src/api-client";
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
    [TransmissionType.Automatic]: "Автомат",
    [TransmissionType.Mechanics]: "Механика",
  };

  return dict[x];
}

export const formatRoubles = (amount: number) =>
  new Intl.NumberFormat("ru-RU", {
    style: "currency",
    maximumSignificantDigits: 3,
    currency: "RUB",
  }).format(amount);

export function formatWorkingTime(hours: number, minutes: number) {
  return `${format(new Date(2000, 0, 0, hours, minutes), "HH:mm")}`;
}

export const getDayOfWeekDisplayName = (day: DayOfWeek) => {
  const daysOfWeek = {
    [DayOfWeek.Monday]: "понедельник",
    [DayOfWeek.Tuesday]: "вторник",
    [DayOfWeek.Wednesday]: "среда",
    [DayOfWeek.Thursday]: "четверг",
    [DayOfWeek.Friday]: "пятница",
    [DayOfWeek.Saturday]: "суббота",
    [DayOfWeek.Sunday]: "воскресенье",
  };
  return daysOfWeek[day];
};
