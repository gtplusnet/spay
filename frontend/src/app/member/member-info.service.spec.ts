import { TestBed, inject } from '@angular/core/testing';

import { MemberInfoService } from './member-info.service';

describe('MemberInfoService', () => {
  beforeEach(() => {
    TestBed.configureTestingModule({
      providers: [MemberInfoService]
    });
  });

  it('should be created', inject([MemberInfoService], (service: MemberInfoService) => {
    expect(service).toBeTruthy();
  }));
});
