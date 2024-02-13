import { atom } from "recoil";
import { User } from "./api-client";

const userAtom = atom<User>({
  key: "userAtom", // unique ID (with respect to other atoms/selectors)
  default: undefined, // default value  
});

export { userAtom };
