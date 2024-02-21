import {  Client } from "./api-client";
// 77.222.56.111
const client = new Client("https://api.gar77.ru/api", {
  fetch: async (url, options) => {
    try {
      const result = await fetch(url, {
        ...options,
        headers: {
          ...options?.headers,
          Authorization: `Bearer ${(window as any).token}`,
        },
      });

      return result;
    } catch (error) {
        console.log("Uh oh!")
        throw error;
    }
  },
});

export { client };
