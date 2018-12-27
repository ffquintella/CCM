using System;
using System.Net;
using System.IO;
using System.Runtime.Serialization.Json;

namespace FGV.ESI.ConnectionString
{
	public class Manager
	{
		public Manager ()
		{
		}

		/// <summary>
		/// Gets the autentication token.
		/// </summary>
		/// <returns>The autentication token.</returns>
		/// <param name="username">Username.</param>
		/// <param name="password">Password.</param>
		public string getAuthenticationToken(string username, string password){

			string url = String.Format("http://localhost/api/authenticationLogin?username={0}&password={1}",username, password);

			Uri serviceUri = new Uri(url);
			WebClient downloader = new WebClient();

			byte[] response = null;
			int respCode = 0;
			HttpWebResponse hwresponse;

			try{
				response = downloader.DownloadData(serviceUri);
			}catch(WebException we)
			{
				hwresponse = (System.Net.HttpWebResponse)we.Response;
				respCode = (int)hwresponse.StatusCode;
			}

			if(respCode != 0 ) return "Código de resposta:" + respCode;


			DataContractJsonSerializer serializer = 
				new DataContractJsonSerializer(typeof(authenticationToken));
			authenticationToken at = (authenticationToken)serializer.ReadObject(new MemoryStream(response));

			return at.tokenValue;
		}

		/// <summary>
		/// Gets the connection string.
		/// </summary>
		/// <returns>The connection string.</returns>
		/// <param name="autenticationToken">Autentication Token.</param>
		public string getConnectionString(string autenticationToken){

			if(autenticationToken == null || autenticationToken.Length == 0) 
				throw new Exception("Cannot have null or empty authentication tokens");

			string url = String.Format("http://localhost/api/databases/Banco_Testes?connectionString=true&XDEBUG_SESSION_START=gubddev");
		

			Uri serviceUri = new Uri(url);
			WebClient downloader = new WebClient();

			// Setting the autenticationToken
			downloader.Headers.Add (HttpRequestHeader.Authorization,  autenticationToken);

			Console.Write("Resulting Request Headers: ");
			Console.WriteLine(downloader.Headers.ToString());

			string response = "";
			int respCode = 0;
			HttpWebResponse hwresponse;

			try{
			 	response = downloader.DownloadString(serviceUri);
			}catch(WebException we)
			{
				hwresponse = (System.Net.HttpWebResponse)we.Response;
				if (hwresponse.StatusCode==HttpStatusCode.NotFound)
					System.Diagnostics.Debug.WriteLine("Not found!");
				respCode = (int)hwresponse.StatusCode;
			}

			response += respCode;

			return response;



		}
	}
}

