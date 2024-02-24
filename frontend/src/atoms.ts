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
const activeBookingAtom = atom<Booking>({
  key: "activeBookingAtom",
  default: undefined,
});
export { userAtom, cityAtom, activeBookingAtom };
