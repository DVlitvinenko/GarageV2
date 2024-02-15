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
import { faTaxi, faUser } from "@fortawesome/free-solid-svg-icons";

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
          <img className="w-24 mb-2" src={logo} />
        </a>
        <span className="font-bold text-md text-gray">Москва</span>
      </div>

      {!!user && <Menu />}
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

const Menu = () => (
  <div className="flex justify-around mx-auto my-2 mb-12 cursor-pointer w-60">
    <Link className="hover:text-sky-400" to="/">
      {/* <img className="object-scale-down w-8 h-8" src={search} /> */}
      <FontAwesomeIcon icon={faTaxi} />
    </Link>
    <Link className="hover:text-sky-400" to="account">
      {/* <img className="object-scale-down w-8 h-8" src={account} /> */}
      <FontAwesomeIcon icon={faUser} />
    </Link>
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
