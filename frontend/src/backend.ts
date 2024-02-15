import {  Client } from "./api-client";

const client = new Client("https://garage.development.kwol.ru/api", {
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
