import { atom } from "recoil";
import {  User } from "./api-client";

const userAtom = atom<User>({
  key: "userAtom",
  default: undefined,
});
const cityAtom = atom<string>({
  key: "cityAtom",
  default: "Москва",
});
export { userAtom, cityAtom};
