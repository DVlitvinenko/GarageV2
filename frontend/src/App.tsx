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
  faClockRotateLeft,
  faRightToBracket,
  faTaxi,
  faUser,
} from "@fortawesome/free-solid-svg-icons";
import { User } from "./api-client";
import { CityPicker } from "./CityPicker";
import { BookingDrawer } from "./BookingDrawer";
import { BookingTimer } from "./BookingTimer";

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
    <div className="max-w-sm p-4 mx-auto sm:max-w-4xl">
      <div className="flex justify-between my-0 space-x-2">
        <Menu user={user} />
        {/* <span className="font-bold text-md text-gray"></span> */}
        <CityPicker />
      </div>
      <BookingTimer />
      <Routes>
        <Route path="/" element={<Finder />} />
        <Route path="account" element={<Account user={user} />} />
        <Route path="bookings" element={<BookingDrawer />} />
        <Route path="login/driver" element={<DriverLogin />} />
        <Route path="login/manager" element={<ManagerLogin />} />
        <Route path="login/admin" element={<AdminLogin />} />
      </Routes>
    </div>
  );
}

export default App;
// const LogoutHandler = () => {
//   client.logout();
//   localStorage.clear();
//   window.location.href = "/";
// };

const Menu = ({ user }: { user: User }) => (
  <div className="flex mx-auto space-x-4 cursor-pointer justify-evenly w-60 sm:justify-start sm:mx-0 sm:w-full sm:space-x-8">
    <Link to="/">
      <img className="h-5 sm:h-7" src={logo} alt="logo" />
    </Link>
    <Link className="hover:text-yellow" to="/">
      <FontAwesomeIcon icon={faTaxi} className="h-4 sm:h-5" />
    </Link>{" "}
    {user && (
      <Link
        className="hover:text-yellow"
        to={user ? "account" : "login/driver"}
      >
        <FontAwesomeIcon icon={faUser} className="h-4 sm:h-5" />
      </Link>
    )}
    {user && (
      <Link className="hover:text-yellow" to="bookings">
        <FontAwesomeIcon icon={faClockRotateLeft} className="h-4 sm:h-5" />
      </Link>
    )}
    {!user && (
      <Link className="hover:text-yellow" to="login/driver">
        <FontAwesomeIcon icon={faRightToBracket} className="h-4 sm:h-5" />
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
