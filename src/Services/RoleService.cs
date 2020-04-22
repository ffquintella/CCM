using System.Collections.Generic;
using System.Net;
using Domain;
using RestSharp;
using Services.Helpers;

namespace Services
{
    public class RoleService: BaseService
    {
        public List<Role> GetAll()
        {
            var client = RestClientHelper.GetAuthenticatedClient();
            var request = new RestRequest("/Roles");

            var response = client.Get<List<Role>>(request);

            if (response.StatusCode == HttpStatusCode.OK)
            {
                return response.Data;
            }
            else
            {
                return null;
            }

        }
    }
}