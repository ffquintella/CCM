using System;
using System.Collections.Generic;
using System.Net;
using System.Threading.Tasks;
using Domain;
using Domain.Protocol;
using RestSharp;
using Serilog;
using Services.Helpers;

namespace Services
{
    public class UserGroupService: BaseService
    {
        public List<UserGroup> GetAll()
        {
            var client = RestClientHelper.GetAuthenticatedClient();
            var request = new RestRequest("/UserGroups");

            var response = client.Get<List<UserGroup>>(request);

            if (response.StatusCode == HttpStatusCode.OK)
            {
                return response.Data;
            }
            else
            {
                return null;
            }

        }
        
        public async Task<List<UserGroup>> GetAllAsync()
        {
            var client = RestClientHelper.GetAuthenticatedClient();
            var request = new RestRequest("/UserGroups");

            try
            {
                var response = await client.GetAsync<List<UserGroup>>(request);
                return response;
            }
            catch (Exception Ex)
            {
                logger.Error("Error getting userGroups:", Ex.Message);
                return null;
            }
        }

        public bool Exists(long id)
        {
            var client = RestClientHelper.GetAuthenticatedClient();
            var request = new RestRequest(string.Format("/UserGroups/{0}/exists", id));

            var response = client.Get(request);
            if (response.StatusCode == HttpStatusCode.OK) return true;
            else return false;

        }
        
        public bool NameExists(string name)
        {
            var client = RestClientHelper.GetAuthenticatedClient();
            var request = new RestRequest("/UserGroups/findOne");
            request.AddParameter("name", name, ParameterType.QueryString);

            var response = client.Get(request);
            if (response.StatusCode == HttpStatusCode.OK) return true;
            else return false;

        }
        
        public ObjectOperationResponse Create(UserGroup group)
        {
            var client = RestClientHelper.GetAuthenticatedClient();
            var request = new RestRequest("/UserGroups");

            request.AddJsonBody(group);
            
            var response = client.Post<ObjectOperationResponse>(request);

            if (response.StatusCode == HttpStatusCode.Created)
            {
                return response.Data;
            }
            else
            {
                return null;
            }

        }
        
        public ObjectOperationResponse Update(UserGroup group)
        {
            var client = RestClientHelper.GetAuthenticatedClient();
            var request = new RestRequest(string.Format("/UserGroups/{0}", group.Id));

            request.AddJsonBody(group);
            
            var response = client.Put<ObjectOperationResponse>(request);
            
            if (response.IsSuccessful)
            {
                return response.Data;
            }
            else
            {
                return null;
            }

        }

        public ObjectOperationResponse Delete(long groupId)
        {
            var client = RestClientHelper.GetAuthenticatedClient();
            
            if (groupId < 0 )
            {
                return new ObjectOperationResponse()
                {
                    Status = ObjectOperationStatus.Error,
                    Message = "Id invalid"
                };
            }

            IRestResponse<ObjectOperationResponse> result;
            
            var request = new RestRequest(string.Format("/UserGroups/{0}", groupId));

            result = client.Delete<ObjectOperationResponse>(request);

            if (result.StatusCode == HttpStatusCode.Accepted)
            {
                return result.Data;
            }

            if (result.StatusCode == HttpStatusCode.NotFound)
            {
                return new ObjectOperationResponse()
                {
                    IdRef = groupId,
                    Message = "Id not found",
                    Status = ObjectOperationStatus.NotFound
                };
            }
            
            return new ObjectOperationResponse()
            {
                IdRef = groupId,
                Message = "Unkwon error",
                Status = ObjectOperationStatus.Error
            };
        }
        
    }
}