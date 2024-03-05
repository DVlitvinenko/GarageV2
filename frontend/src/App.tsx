import { useEffect } from "react";
// import logo from "./assets/mon-garage.svg";
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
    <div className="max-w-sm p-4 mx-auto sm:max-w-[800px] lg:max-w-[1104px]">
      <div className="flex justify-between my-0 space-x-2">
        <Menu user={user} />
        {/* <span className="font-bold text-md text-gray"></span> */}
      </div>
      <Link to="bookings">
        <BookingTimer />
      </Link>
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
  <div className="flex justify-between w-full space-x-4 cursor-pointer sm:mx-0 sm:w-full sm:space-x-8 sm:max-w-[800px] lg:max-w-[1104px] sm:justify-between">
    <Link to="/">
      <div className="flex items-center text-sm font-black tracking-widest sm:text-xl">
        МОЙ ГАРАЖ
      </div>
      {/* <img className="h-5 sm:h-7" src={logo} alt="logo" /> */}
    </Link>
    {/* <Link className="hover:text-yellow" to="/">
      <FontAwesomeIcon icon={faTaxi} className="h-4 sm:h-5" />
    </Link>{" "} */}
    {user && (
      <Link
        className="flex items-center hover:text-yellow"
        to={user ? "account" : "login/driver"}
      >
        <FontAwesomeIcon icon={faUser} className="h-4 sm:h-5 md:hidden" />
        <div className="hidden text-xl font-semibold md:block">Кабинет</div>
      </Link>
    )}
    {user && (
      <Link className="flex items-center hover:text-yellow" to="bookings">
        <FontAwesomeIcon
          icon={faClockRotateLeft}
          className="h-4 sm:h-5 md:hidden"
        />
        <div className="hidden text-xl font-semibold md:block">Бронь</div>
      </Link>
    )}
    <div className="flex items-center md:ml-auto md:grow md:flex md:justify-end">
      <CityPicker />
    </div>
    {!user && (
      <Link className="flex items-center hover:text-yellow" to="login/driver">
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
