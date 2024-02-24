import { atom } from "recoil";
import { Booking, User } from "./api-client";

const userAtom = atom<User>({
  key: "userAtom",
  default: undefined,
});
const cityAtom = atom<string>({
  key: "cityAtom",
  default: "Москва",
});
const isActiveBookingAtom = atom({
  key: "isActiveBooking",
  default: false,
});
export { userAtom, cityAtom, isActiveBookingAtom };
