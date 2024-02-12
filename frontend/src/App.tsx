import { useEffect, useState } from "react";
import logo from "./assets/logo.png";
import search from "./assets/search.svg";
import account from "./assets/account.svg";
import "./App.css";
import { Link, Route, Routes, useNavigate } from "react-router-dom";
import { client } from "./backend";
import { CarFinder } from "./CarFinder";
import { Account } from "./Account";
import { DriverLogin } from "./DriverLogin";

function App() {
  const [count, setCount] = useState(0);
  const [user, setUser] = useState<any>();
  const navigate = useNavigate();

  useEffect(() => {
    const checkAuth = async () => {
      const token = localStorage.getItem("token");
      if (token) {
        (window as any).token = token;

        try {
          const userData = await client.getUser();
          setUser(userData);
        } catch (error) {
          navigate("/login/driver");
        }
      }
    };

    checkAuth();
  }, []);

  return (
    <>
      <a>
        <img className="mx-auto my-8" src={logo} />
      </a>
      <Menu />
      <Routes>
        <Route path="/" element={<CarFinder />} />
        <Route path="account" element={<Account />} />
        <Route path="login/driver" element={<DriverLogin />} />
        <Route path="login/manager" element={<ManagerLogin />} />
        <Route path="login/admin" element={<AdminLogin />} />
      </Routes>
    </>
  );
}

export default App;

const Menu = () => (
  <div className="flex justify-around mx-auto my-2 mb-12 cursor-pointer w-60">
    <Link className="hover:text-sky-400" to="/">
      <img className="object-scale-down w-8 h-8" src={search} />
    </Link>
    <Link className="hover:text-sky-400" to="account">
      <img className="object-scale-down w-8 h-8" src={account} />
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
