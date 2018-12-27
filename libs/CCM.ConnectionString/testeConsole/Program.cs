using System;
using FGV.ESI.ConnectionString;

namespace testeConsole
{
	class MainClass
	{
		public static void Main (string[] args)
		{
			Console.WriteLine ("Inicializando teste de conexão webservices...");

			Manager csm = new Manager();

			string token = csm.getAuthenticationToken ("Stestes", "teste");

			Console.WriteLine ("Token de autenticação:" + token);

			string connectionString = csm.getConnectionString (token);

			int i = 0 ;

			while (i < 4 && connectionString == "401") {
				token = csm.getAuthenticationToken ("Stestes", "teste");

				Console.WriteLine ("Tentato com novo token de autenticação:" + token);

				connectionString = csm.getConnectionString (token);

				i++; 
			}



			Console.WriteLine ("String de conexão:" + connectionString);
		}
	}
}
