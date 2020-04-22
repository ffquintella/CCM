using System;
using System.Collections.Generic;
using System.Net;
using Domain;
using Domain.Protocol;
using Microsoft.AspNetCore.Components;
using Microsoft.Extensions.Logging;
using RestSharp;
using Services.Authentication;
using Services.Helpers;

namespace Services
{
    public class UserService: BaseService
    {
        public List<User> GetAllUsers()
        {
            var client = RestClientHelper.GetAuthenticatedClient();
            var request = new RestRequest("/Users");

            var response = client.Get<List<User>>(request);

            if (response.IsSuccessful)
            {
                return response.Data;
            }
            else
            {
                return null;
            }

        }
        
        public List<User> GetUsersInList(List<long> userIds)
        {
            var client = RestClientHelper.GetAuthenticatedClient();
            var request = new RestRequest("/Users/listGet");

            request.AddJsonBody(userIds);

            var response = client.Post<List<User>>(request);

            if (response.IsSuccessful)
            {
                return response.Data;
            }
            else
            {
                return null;
            }

        }

        public ObjectOperationResponse SaveUser(User user)
        {

            var client = RestClientHelper.GetAuthenticatedClient();
            
            if (user == null)
            {
                return new ObjectOperationResponse()
                {
                    Status = ObjectOperationStatus.Error,
                    Message = "User cannot be null"
                };
            }

            IRestResponse<ObjectOperationResponse> result;
            
            if (user.Id == -1)
            {
                var request = new RestRequest("/Users");
                request.AddJsonBody(user);
                result = client.Post<ObjectOperationResponse>(request);
            }
            else
            {
                var request = new RestRequest(string.Format("/Users/{0}", user.Id));
                request.AddJsonBody(user);
                result = client.Put<ObjectOperationResponse>(request);
            }
            
            if (result.StatusCode == HttpStatusCode.OK || result.StatusCode == HttpStatusCode.Created)
            {
                return result.Data;
            }
            else
            {
                switch (result.StatusCode)
                {
                    case HttpStatusCode.Forbidden:
                        return new ObjectOperationResponse()
                        {
                            Status = ObjectOperationStatus.Forbidden,
                        };
                    case HttpStatusCode.NotFound:
                        return new ObjectOperationResponse()
                        {
                            Status = ObjectOperationStatus.NotFound,
                        };
                }
            }
            
            return new ObjectOperationResponse()
            {
                Status = ObjectOperationStatus.Error,
                Message = "Unkwon error"
            };
        }

        public ObjectOperationResponse Delete(long userid)
        {
            var client = RestClientHelper.GetAuthenticatedClient();
            
            if (userid < 0 )
            {
                return new ObjectOperationResponse()
                {
                    Status = ObjectOperationStatus.Error,
                    Message = "Id invalid"
                };
            }

            IRestResponse<ObjectOperationResponse> result;
            
            var request = new RestRequest(string.Format("/Users/{0}", userid));

            result = client.Delete<ObjectOperationResponse>(request);

            if (result.StatusCode == HttpStatusCode.Accepted)
            {
                return result.Data;
            }

            if (result.StatusCode == HttpStatusCode.NotFound)
            {
                return new ObjectOperationResponse()
                {
                    IdRef = userid,
                    Message = "Id not found",
                    Status = ObjectOperationStatus.NotFound
                };
            }
            
            return new ObjectOperationResponse()
            {
                IdRef = userid,
                Message = "Unkwon error",
                Status = ObjectOperationStatus.Error
            };
            
        }
        
    }
}