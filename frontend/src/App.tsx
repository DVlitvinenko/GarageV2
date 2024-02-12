import { useEffect, useState } from "react";
import logo from "./assets/logo.png";
import search from "./assets/search.svg";
import account from "./assets/account.svg";
import "./App.css";
import { Link, Route, Routes, useNavigate } from "react-router-dom";
import { client } from "./backend";
import { CarFinder } from "./Finder";
import { Account } from "./Account";
import { DriverLogin } from "./DriverLogin";
import { Card } from "./Card";

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
        <img className="my-8 mx-auto" src={logo} />
      </a>
      <Menu />
      <Routes>
        <Route path="/" element={<CarFinder />} />
        <Route path="/card" element={<Card />} />
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
  <div className="w-60 mx-auto flex justify-around mb-12 cursor-pointer my-2">
    <Link className="hover:text-sky-400" to="/">
      <img className="object-scale-down h-8 w-8" src={search} />
    </Link>
    <Link className="hover:text-sky-400" to="account">
      <img className="object-scale-down h-8 w-8" src={account} />
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
