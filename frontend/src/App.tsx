import { useEffect } from "react";
import logo from "./assets/logo.png";
import "./App.css";
import { Link, Route, Routes } from "react-router-dom";
import { client } from "./backend";
import { Finder } from "./Finder";
import { Account } from "./Account";
import { DriverLogin } from "./DriverLogin";
import { useRecoilState } from "recoil";
import { userAtom } from "./atoms";
import { FontAwesomeIcon } from "@fortawesome/react-fontawesome";
import {
  faRightFromBracket,
  faRightToBracket,
  faTaxi,
  faUser,
} from "@fortawesome/free-solid-svg-icons";
import { User } from "./api-client";
import { CityPicker } from "./CityPicker";
import { LogOut } from "lucide-react";

function App() {
  const [user, setUser] = useRecoilState(userAtom);

  useEffect(() => {
    const checkAuth = async () => {
      const token = localStorage.getItem("token");
      if (token) {
        (window as any).token = token;
        try {
          const userData = await client.getUser();

          setUser(userData.user!);
        } catch (error) {}
      }
    };

    checkAuth();
  }, []);

  return (
    <div className="max-w-sm p-4 mx-auto">
      <div className="flex justify-between my-0">
        <a>
          <img className="w-24 mb-2" src={logo} alt="logo" />
        </a>
        <Menu user={user} />
        <span className="font-bold text-md text-gray"></span>
        <CityPicker />
      </div>

      <Routes>
        <Route path="/" element={<Finder />} />
        <Route path="account" element={<Account user={user} />} />
        <Route path="login/driver" element={<DriverLogin />} />
        <Route path="login/manager" element={<ManagerLogin />} />
        <Route path="login/admin" element={<AdminLogin />} />
      </Routes>
    </div>
  );
}

export default App;

const LogoutHandler = () => {
  client.logout();
  localStorage.clear();
  window.location.href = "/";
};

const Menu = ({ user }: { user: User }) => (
  <div className="flex mx-auto cursor-pointer justify-evenly w-60">
    <Link className="hover:text-sky-400" to="/">
      <FontAwesomeIcon icon={faTaxi} />
    </Link>
    <Link className="hover:text-sky-400" to={user ? "account" : "login/driver"}>
      <FontAwesomeIcon icon={faUser} />
    </Link>
    {user && (
      <div className="hover:text-sky-400" onClick={LogoutHandler}>
        <FontAwesomeIcon icon={faRightFromBracket} />
      </div>
    )}
    {!user && (
      <Link className="hover:text-sky-400" to="login/driver">
        <FontAwesomeIcon icon={faRightToBracket} />
      </Link>
    )}
  </div>
);

const ManagerLogin = () => (
  <>
    <h1>Manager login page</h1>
  </>
);
const AdminLogin = () => (
  <>
    <h1>Admin login page</h1>
  </>
);
