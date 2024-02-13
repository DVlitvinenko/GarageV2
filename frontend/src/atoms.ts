import { atom } from "recoil";
import { User } from "./api-client";

const userAtom = atom<User>({
  key: "userAtom", // unique ID (with respect to other atoms/selectors)
  default: new User({
    docs: []
  }), // default value  
});

export { userAtom };
